@echo off
echo 🔍 Running Code Quality Checks
echo ==============================
echo.

REM Create results directory
if not exist quality-reports mkdir quality-reports

REM 1. Laravel Pint (Code Formatting)
echo 1️⃣  Running Laravel Pint (Code Formatting)...
echo    Checking code formatting...
vendor\bin\pint --test --config=pint.json > quality-reports\pint-report.txt 2>&1
set PINT_EXIT_CODE=%ERRORLEVEL%

if %PINT_EXIT_CODE%==0 (
    echo    ✅ Pint: All files are properly formatted
) else (
    echo    ⚠️  Pint: Code formatting issues found
    echo    💡 Run: vendor\bin\pint to fix formatting issues
)

echo.

REM 2. PHPStan (Static Analysis)
echo 2️⃣  Running PHPStan (Static Analysis)...
echo    Analyzing code for potential bugs...
vendor\bin\phpstan analyse --memory-limit=1G --no-progress --error-format=table > quality-reports\phpstan-report.txt 2>&1
set PHPSTAN_EXIT_CODE=%ERRORLEVEL%

if %PHPSTAN_EXIT_CODE%==0 (
    echo    ✅ PHPStan: No errors found
) else (
    echo    ❌ PHPStan: Issues found
    echo    📊 Check: quality-reports\phpstan-report.txt
)

echo.

REM 3. PHPInsights (Code Quality)
echo 3️⃣  Running PHPInsights (Code Quality)...
echo    Analyzing code quality metrics...
vendor\bin\phpinsights --no-interaction --format=console > quality-reports\phpinsights-report.txt 2>&1
set INSIGHTS_EXIT_CODE=%ERRORLEVEL%

if %INSIGHTS_EXIT_CODE%==0 (
    echo    ✅ PHPInsights: Quality standards met
) else (
    echo    ⚠️  PHPInsights: Quality improvements needed
    echo    📊 Check: quality-reports\phpinsights-report.txt
)

echo.

REM 4. Test Coverage
echo 4️⃣  Running Tests...
if exist vendor\bin\pest.bat (
    vendor\bin\pest --coverage --coverage-text > quality-reports\test-report.txt 2>&1
    set TEST_EXIT_CODE=%ERRORLEVEL%

    if %TEST_EXIT_CODE%==0 (
        echo    ✅ Tests: All tests passing
    ) else (
        echo    ❌ Tests: Some tests failing
        echo    📊 Check: quality-reports\test-report.txt
    )
) else (
    echo    ℹ️  Pest not found, trying PHPUnit...
    if exist vendor\bin\phpunit.bat (
        vendor\bin\phpunit --coverage-text > quality-reports\test-report.txt 2>&1
        set TEST_EXIT_CODE=%ERRORLEVEL%

        if %TEST_EXIT_CODE%==0 (
            echo    ✅ Tests: All tests passing
        ) else (
            echo    ❌ Tests: Some tests failing
            echo    📊 Check: quality-reports\test-report.txt
        )
    ) else (
        echo    ⚠️  No test framework found
        set TEST_EXIT_CODE=0
    )
)

echo.

REM Summary
echo 📊 Quality Check Summary
echo ========================

set TOTAL_ISSUES=0

if not %PINT_EXIT_CODE%==0 (
    echo ❌ Code Formatting: Issues found
    set /a TOTAL_ISSUES+=1
) else (
    echo ✅ Code Formatting: Passed
)

if not %PHPSTAN_EXIT_CODE%==0 (
    echo ❌ Static Analysis: Issues found
    set /a TOTAL_ISSUES+=1
) else (
    echo ✅ Static Analysis: Passed
)

if not %INSIGHTS_EXIT_CODE%==0 (
    echo ❌ Code Quality: Below standards
    set /a TOTAL_ISSUES+=1
) else (
    echo ✅ Code Quality: Passed
)

if not %TEST_EXIT_CODE%==0 (
    echo ❌ Tests: Failing
    set /a TOTAL_ISSUES+=1
) else (
    echo ✅ Tests: Passed
)

echo.

if %TOTAL_ISSUES%==0 (
    echo 🎉 All quality checks passed!
    echo 🚀 Your code is ready for deployment
    exit /b 0
) else (
    echo ⚠️  Found %TOTAL_ISSUES% issues to address
    echo 📝 Check reports in quality-reports\ directory
    echo.
    echo 🔧 Quick fixes:
    echo    - Format code: vendor\bin\pint
    echo    - Fix insights: vendor\bin\phpinsights --fix
    echo    - View detailed reports in quality-reports\
    exit /b 1
)

pause
