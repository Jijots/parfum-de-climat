"""
Recommendation engine entry point. Supports two modes:

  Subprocess (default): reads a JSON payload from stdin, writes ranked results
  to stdout. Laravel spawns this as a child process via EngineService.

  Microservice (--serve): runs as a FastAPI server. Laravel calls it over HTTP.
  Switch modes via ENGINE_MODE=microservice in .env.

Exit codes (subprocess mode): 0 = success, 1 = validation error, 2 = runtime error.
"""

from __future__ import annotations

import argparse
import json
import sys
from typing import Any

from pydantic import ValidationError

from models import EngineRequest
from scorer import score_collection


def run_subprocess() -> int:
    """Read JSON from stdin, score the collection, write results to stdout."""
    try:
        raw_input = sys.stdin.read()
    except Exception as e:
        _write_error("ReadError", f"Failed to read from stdin: {e}")
        return 2

    if not raw_input.strip():
        _write_error("EmptyInput", "No data received on stdin. Expected a JSON payload.")
        return 1

    try:
        payload_dict = json.loads(raw_input)
    except json.JSONDecodeError as e:
        _write_error("JSONParseError", f"Could not parse stdin as JSON: {e}")
        return 1

    try:
        request = EngineRequest.model_validate(payload_dict)
    except ValidationError as e:
        # Format Pydantic's errors into a readable string for Laravel's log
        error_summary = "; ".join(
            f"{' → '.join(str(loc) for loc in err['loc'])}: {err['msg']}"
            for err in e.errors()
        )
        _write_error("ValidationError", error_summary)
        return 1

    try:
        # mode='json' serialises enums to their string values (e.g. "summer" not Season.SUMMER)
        results = score_collection(request.model_dump(mode="json"))
    except Exception as e:
        _write_error("ScoringError", f"Unexpected error during scoring: {e}")
        return 2

    # Compact separators — no unnecessary whitespace.
    sys.stdout.write(json.dumps(results, separators=(",", ":")))
    sys.stdout.flush()

    return 0


def _write_error(error_type: str, detail: str) -> None:
    """
    Write a structured error JSON to stderr.
    Laravel's EngineService reads stderr on non-zero exit codes.
    """
    error_payload = json.dumps({"error": error_type, "detail": detail})
    sys.stderr.write(error_payload)
    sys.stderr.flush()


def create_app() -> Any:
    """Create the FastAPI app. Imports are deferred so subprocess startup stays fast."""
    try:
        from fastapi import FastAPI
        from fastapi.responses import JSONResponse
    except ImportError:
        print(
            "FastAPI is not installed. Run: pip install fastapi uvicorn\n"
            "Or install all dependencies: pip install -r requirements.txt",
            file=sys.stderr,
        )
        sys.exit(1)

    from models import EngineRequest, EngineResponse, ScoredFragrance

    application = FastAPI(
        title="Parfum de Climat — Recommendation Engine",
        description=(
            "Climate-to-fragrance matching microservice. "
            "Accepts a weather context and a user's fragrance collection, "
            "returns fragrances ranked by climate suitability."
        ),
        version="1.0.0",
        docs_url="/docs",
        redoc_url="/redoc",
    )

    @application.get("/health", tags=["Health"])
    async def health_check() -> dict:
        return {"status": "ok", "service": "parfum-de-climat-engine"}

    @application.post(
        "/recommend",
        response_model=list[ScoredFragrance],
        tags=["Recommendations"],
        summary="Score a fragrance collection against the current weather",
        response_description="All fragrances scored and sorted by climate suitability, descending.",
    )
    async def recommend(request: EngineRequest) -> JSONResponse:
        results = score_collection(request.model_dump())
        return JSONResponse(content=results)

    return application


# Lazily created — None in subprocess mode, populated when running as a server.
# uvicorn discovers it as: uvicorn recommendation_engine:app
app = None



if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        description="Parfum de Climat — Recommendation Engine",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  Subprocess mode (pipe JSON):
    echo '{"weather":{...},"collection":[...]}' | python recommendation_engine.py

  Start as FastAPI microservice on port 8001:
    python recommendation_engine.py --serve
    python recommendation_engine.py --serve --host 0.0.0.0 --port 8001

  Run with uvicorn directly (production):
    uvicorn recommendation_engine:app --host 127.0.0.1 --port 8001 --workers 2
        """,
    )
    parser.add_argument(
        "--serve",
        action="store_true",
        help="Run as a FastAPI microservice instead of a subprocess script.",
    )
    parser.add_argument(
        "--host",
        default="127.0.0.1",
        help="Host to bind the microservice to (default: 127.0.0.1).",
    )
    parser.add_argument(
        "--port",
        type=int,
        default=8001,
        help="Port to bind the microservice to (default: 8001).",
    )

    args = parser.parse_args()

    if args.serve:
        # Microservice mode
        try:
            import uvicorn
        except ImportError:
            print(
                "uvicorn is not installed. Run: pip install uvicorn\n"
                "Or install all dependencies: pip install -r requirements.txt",
                file=sys.stderr,
            )
            sys.exit(1)

        app = create_app()

        print(f"Starting Parfum de Climat Engine on http://{args.host}:{args.port}")
        print(f"  API docs: http://{args.host}:{args.port}/docs")
        print(f"  Health:   http://{args.host}:{args.port}/health")

        uvicorn.run(app, host=args.host, port=args.port)
    else:
        exit_code = run_subprocess()
        sys.exit(exit_code)
else:
    # Imported by uvicorn — create the app at import time.
    app = create_app()
