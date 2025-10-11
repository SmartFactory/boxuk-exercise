#!/bin/bash
#
# PHPUnit Test Runner for DDEV
#
# Usage:
#   ./test                          # Run all custom module tests
#   ./test unit                     # Run only unit tests
#   ./test kernel                   # Run only kernel tests
#   ./test module <name>            # Run tests for specific module
#   ./test file <path>              # Run specific test file
#   ./test filter <pattern>         # Run tests matching pattern
#
# Examples:
#   ./test
#   ./test unit
#   ./test module boxuk_patterns
#   ./test file web/modules/boxuk_patterns/tests/src/Unit/Pipe/PipelineTest.php
#   ./test filter testHandleReturnsTitleData
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if we're in DDEV
if ! command -v ddev &> /dev/null; then
    echo -e "${RED}Error: DDEV is not installed or not in PATH${NC}"
    exit 1
fi

# Check if DDEV is running
if ! ddev describe &> /dev/null; then
    echo -e "${YELLOW}Starting DDEV...${NC}"
    ddev start
fi

# Function to run phpunit with proper settings
run_phpunit() {
    echo -e "${BLUE}Running tests...${NC}"
    ddev exec "cd web && phpunit $*"
}

# Parse command
case "${1:-all}" in
    # Run all custom module tests
    all|"")
        echo -e "${GREEN}Running all custom module tests (unit + kernel)${NC}"
        run_phpunit --testsuite custom-unit --testsuite custom-kernel
        ;;

    # Run only unit tests
    unit|u)
        echo -e "${GREEN}Running custom module unit tests${NC}"
        run_phpunit --testsuite custom-unit
        ;;

    # Run only kernel tests
    kernel|k)
        echo -e "${GREEN}Running custom module kernel tests${NC}"
        run_phpunit --testsuite custom-kernel
        ;;

    # Run only functional tests
    functional|f)
        echo -e "${GREEN}Running custom module functional tests${NC}"
        run_phpunit --testsuite custom-functional
        ;;

    # Run tests for specific module
    module|mod|m)
        if [ -z "$2" ]; then
            echo -e "${RED}Error: Module name required${NC}"
            echo "Usage: ./test module <module_name>"
            exit 1
        fi
        echo -e "${GREEN}Running tests for module: $2${NC}"
        run_phpunit "modules/$2"
        ;;

    # Run specific test file
    file)
        if [ -z "$2" ]; then
            echo -e "${RED}Error: File path required${NC}"
            echo "Usage: ./test file <path/to/TestFile.php>"
            exit 1
        fi
        # Remove 'web/' prefix if present
        FILE_PATH="${2#web/}"
        echo -e "${GREEN}Running test file: $FILE_PATH${NC}"
        run_phpunit "$FILE_PATH"
        ;;

    # Run tests matching a filter pattern
    filter)
        if [ -z "$2" ]; then
            echo -e "${RED}Error: Filter pattern required${NC}"
            echo "Usage: ./test filter <pattern>"
            exit 1
        fi
        echo -e "${GREEN}Running tests matching: $2${NC}"
        run_phpunit --filter "$2"
        ;;

    # Run with coverage
    coverage)
        echo -e "${GREEN}Running tests with coverage report${NC}"
        run_phpunit --testsuite custom-unit --testsuite custom-kernel --coverage-html coverage
        echo -e "${YELLOW}Coverage report generated in: coverage/index.html${NC}"
        ;;

    # Show help
    help|--help|-h)
        echo -e "${BLUE}PHPUnit Test Runner for DDEV${NC}"
        echo ""
        echo "Usage: ./test [command] [options]"
        echo ""
        echo "Commands:"
        echo "  (none)              Run all custom module tests"
        echo "  unit, u             Run only unit tests (fast)"
        echo "  kernel, k           Run only kernel tests"
        echo "  functional, f       Run only functional tests"
        echo "  module, m <name>    Run tests for specific module"
        echo "  file <path>         Run specific test file"
        echo "  filter <pattern>    Run tests matching pattern"
        echo "  coverage            Run tests with coverage report"
        echo "  help                Show this help message"
        echo ""
        echo "Examples:"
        echo "  ./test                                    # All custom module tests"
        echo "  ./test unit                               # Only unit tests"
        echo "  ./test kernel                             # Only kernel tests"
        echo "  ./test module boxuk_patterns              # Tests for one module"
        echo "  ./test file modules/boxuk_patterns/tests/src/Unit/Pipe/PipelineTest.php"
        echo "  ./test filter testHandleReturnsTitleData  # Specific test method"
        echo "  ./test coverage                           # Generate coverage report"
        ;;

    *)
        echo -e "${RED}Unknown command: $1${NC}"
        echo "Run './test help' for usage information"
        exit 1
        ;;
esac
