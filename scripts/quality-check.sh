#!/bin/bash

echo "🔍 Running Code Quality Checks"
echo "=============================="
echo ""

# Create results directory
mkdir -p quality-reports

# 1. Laravel Pint (Code Formatting)
echo "1️⃣  Running Laravel Pint (Code Formatting)..."
echo "   Checking code formatting..."
vendor/bin/pint --test --config=pint.json > quality-reports/pint-report.txt 2>&1
PINT_EXIT_CODE=$?

if [ $PINT_EXIT_CODE -eq 0 ]; then
    echo "   ✅ Pint: All files are properly formatted"
else
    echo "   ⚠️  Pint: Code formatting issues found"
    echo "   💡 Run: vendor/bin/pint to fix formatting issues"
fi

echo ""

# 2. PHPStan (Static Analysis)
echo "2️⃣  Running PHPStan (Static Analysis)..."
echo "   Analyzing code for potential bugs..."
vendor/bin/phpstan analyse --memory-limit=1G --no-progress --error-format=table > quality-reports/phpstan-report.txt 2>&1
PHPSTAN_EXIT_CODE=$?

if [ $PHPSTAN_EXIT_CODE -eq 0 ]; then
    echo "   ✅ PHPStan: No errors found"
else
    echo "   ❌ PHPStan: Issues found"
    echo "   📊 Check: quality-reports/phpstan-report.txt"
fi

echo ""

# 3. PHPInsights (Code Quality)
echo "3️⃣  Running PHPInsights (Code Quality)..."
echo "   Analyzing code quality metrics..."
vendor/bin/phpinsights --no-interaction --format=console > quality-reports/phpinsights-report.txt 2>&1
INSIGHTS_EXIT_CODE=$?

if [ $INSIGHTS_EXIT_CODE -eq 0 ]; then
    echo "   ✅ PHPInsights: Quality standards met"
else
    echo "   ⚠️  PHPInsights: Quality improvements needed"
    echo "   📊 Check: quality-reports/phpinsights-report.txt"
fi

echo ""

# 4. Security Check (if available)
echo "4️⃣  Running Security Check..."
if command -v security-checker &> /dev/null; then
    security-checker security:check composer.lock > quality-reports/security-report.txt 2>&1
    SECURITY_EXIT_CODE=$?

    if [ $SECURITY_EXIT_CODE -eq 0 ]; then
        echo "   ✅ Security: No known vulnerabilities"
    else
        echo "   ⚠️  Security: Potential vulnerabilities found"
        echo "   📊 Check: quality-reports/security-report.txt"
    fi
else
    echo "   ℹ️  Security Checker not installed"
    echo "   💡 Install: composer require --dev enlightn/security-checker"
fi

echo ""

# 5. Test Coverage (if tests exist)
echo "5️⃣  Running Tests..."
if [ -f "vendor/bin/pest" ]; then
    vendor/bin/pest --coverage --coverage-text > quality-reports/test-report.txt 2>&1
    TEST_EXIT_CODE=$?

    if [ $TEST_EXIT_CODE -eq 0 ]; then
        echo "   ✅ Tests: All tests passing"
    else
        echo "   ❌ Tests: Some tests failing"
        echo "   📊 Check: quality-reports/test-report.txt"
    fi
else
    echo "   ℹ️  Pest not found, trying PHPUnit..."
    if [ -f "vendor/bin/phpunit" ]; then
        vendor/bin/phpunit --coverage-text > quality-reports/test-report.txt 2>&1
        TEST_EXIT_CODE=$?

        if [ $TEST_EXIT_CODE -eq 0 ]; then
            echo "   ✅ Tests: All tests passing"
        else
            echo "   ❌ Tests: Some tests failing"
            echo "   📊 Check: quality-reports/test-report.txt"
        fi
    else
        echo "   ⚠️  No test framework found"
    fi
fi

echo ""

# Summary
echo "📊 Quality Check Summary"
echo "========================"

TOTAL_ISSUES=0

if [ $PINT_EXIT_CODE -ne 0 ]; then
    echo "❌ Code Formatting: Issues found"
    TOTAL_ISSUES=$((TOTAL_ISSUES + 1))
else
    echo "✅ Code Formatting: Passed"
fi

if [ $PHPSTAN_EXIT_CODE -ne 0 ]; then
    echo "❌ Static Analysis: Issues found"
    TOTAL_ISSUES=$((TOTAL_ISSUES + 1))
else
    echo "✅ Static Analysis: Passed"
fi

if [ $INSIGHTS_EXIT_CODE -ne 0 ]; then
    echo "❌ Code Quality: Below standards"
    TOTAL_ISSUES=$((TOTAL_ISSUES + 1))
else
    echo "✅ Code Quality: Passed"
fi

if [ ${TEST_EXIT_CODE:-0} -ne 0 ]; then
    echo "❌ Tests: Failing"
    TOTAL_ISSUES=$((TOTAL_ISSUES + 1))
else
    echo "✅ Tests: Passed"
fi

echo ""

if [ $TOTAL_ISSUES -eq 0 ]; then
    echo "🎉 All quality checks passed!"
    echo "🚀 Your code is ready for deployment"
    exit 0
else
    echo "⚠️  Found $TOTAL_ISSUES issues to address"
    echo "📝 Check reports in quality-reports/ directory"
    echo ""
    echo "🔧 Quick fixes:"
    echo "   - Format code: vendor/bin/pint"
    echo "   - Fix insights: vendor/bin/phpinsights --fix"
    echo "   - View detailed reports in quality-reports/"
    exit 1
fi
