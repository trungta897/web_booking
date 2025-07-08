#!/bin/bash

# 🔔 VNPay IPN Helper Script
# Tiện ích để test và monitor VNPay IPN

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DOMAIN="${1:-localhost}"
PROTOCOL="https"
if [[ "$DOMAIN" == "localhost" ]]; then
    PROTOCOL="http"
fi

IPN_URL="${PROTOCOL}://${DOMAIN}/payments/vnpay/ipn"
RETURN_URL="${PROTOCOL}://${DOMAIN}/payments/vnpay/return"

print_header() {
    echo -e "${BLUE}🔔 VNPay IPN Helper Tool${NC}"
    echo -e "${BLUE}=========================${NC}"
    echo ""
}

print_urls() {
    echo -e "${YELLOW}📋 VNPay URLs Configuration:${NC}"
    echo -e "   IPN URL: ${GREEN}${IPN_URL}${NC}"
    echo -e "   Return URL: ${GREEN}${RETURN_URL}${NC}"
    echo ""
}

test_ipn_endpoint() {
    echo -e "${YELLOW}🧪 Testing IPN Endpoint...${NC}"

    # Test data
    TEST_DATA="vnp_Amount=10000000&vnp_BankCode=NCB&vnp_ResponseCode=00&vnp_TxnRef=TEST_$(date +%s)&vnp_TransactionNo=12345678&vnp_PayDate=$(date +%Y%m%d%H%M%S)"

    echo "   Sending test data to: $IPN_URL"

    RESPONSE=$(curl -s -w "%{http_code}" -X POST "$IPN_URL" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "$TEST_DATA" \
        -o /tmp/ipn_response.txt)

    HTTP_CODE="${RESPONSE: -3}"
    RESPONSE_BODY=$(cat /tmp/ipn_response.txt)

    if [[ "$HTTP_CODE" == "200" ]]; then
        echo -e "   ${GREEN}✅ IPN endpoint responds correctly (HTTP $HTTP_CODE)${NC}"
        echo -e "   Response: $RESPONSE_BODY"
    else
        echo -e "   ${RED}❌ IPN endpoint error (HTTP $HTTP_CODE)${NC}"
        echo -e "   Response: $RESPONSE_BODY"
    fi

    rm -f /tmp/ipn_response.txt
    echo ""
}

check_ngrok() {
    echo -e "${YELLOW}🔍 Checking ngrok status...${NC}"

    if command -v ngrok &> /dev/null; then
        echo -e "   ${GREEN}✅ ngrok is installed${NC}"

        # Check if ngrok is running
        if curl -s http://localhost:4040/api/tunnels &> /dev/null; then
            NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o '"public_url":"https://[^"]*' | cut -d'"' -f4 | head -1)
            if [[ ! -z "$NGROK_URL" ]]; then
                echo -e "   ${GREEN}✅ ngrok tunnel active: $NGROK_URL${NC}"
                echo -e "   ${BLUE}📋 Use these URLs in VNPay Portal:${NC}"
                echo -e "      IPN URL: ${GREEN}$NGROK_URL/payments/vnpay/ipn${NC}"
                echo -e "      Return URL: ${GREEN}$NGROK_URL/payments/vnpay/return${NC}"
            else
                echo -e "   ${YELLOW}⚠️  ngrok is running but no tunnel found${NC}"
            fi
        else
            echo -e "   ${YELLOW}⚠️  ngrok is not running${NC}"
            echo -e "   Run: ${BLUE}ngrok http 80${NC} (for nginx) or ${BLUE}ngrok http 8000${NC} (for dev server)"
        fi
    else
        echo -e "   ${RED}❌ ngrok not installed${NC}"
        echo -e "   Install: ${BLUE}npm install -g ngrok${NC}"
    fi
    echo ""
}

monitor_logs() {
    echo -e "${YELLOW}📊 VNPay IPN Log Monitoring...${NC}"

    LOG_FILE="storage/logs/laravel.log"

    if [[ ! -f "$LOG_FILE" ]]; then
        echo -e "   ${RED}❌ Laravel log file not found: $LOG_FILE${NC}"
        return
    fi

    # Count IPNs today
    TODAY=$(date '+%Y-%m-%d')
    IPN_TODAY=$(grep "$TODAY" "$LOG_FILE" | grep "VNPay IPN received" | wc -l)
    IPN_SUCCESS=$(grep "$TODAY" "$LOG_FILE" | grep "VNPay IPN processed successfully" | wc -l)
    IPN_FAILED=$(grep "$TODAY" "$LOG_FILE" | grep "VNPay IPN handling failed" | wc -l)

    echo -e "   📈 IPN calls today: ${BLUE}$IPN_TODAY${NC}"
    echo -e "   ✅ Successful: ${GREEN}$IPN_SUCCESS${NC}"
    echo -e "   ❌ Failed: ${RED}$IPN_FAILED${NC}"
    echo ""

    # Latest IPN entries
    echo -e "   ${YELLOW}🕒 Latest IPN log entries:${NC}"
    grep "VNPay IPN" "$LOG_FILE" | tail -5 | while read line; do
        echo -e "      $line"
    done
    echo ""
}

generate_env_config() {
    echo -e "${YELLOW}⚙️  VNPay Environment Configuration:${NC}"
    echo ""
    echo "# Add these to your .env file:"
    echo "VNPAY_TMN_CODE=your_vnpay_terminal_code"
    echo "VNPAY_HASH_SECRET=your_vnpay_hash_secret"
    echo "VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"
    echo "VNPAY_RETURN_URL=${RETURN_URL}"
    echo "VNPAY_IPN_URL=${IPN_URL}"
    echo ""
}

check_env_config() {
    echo -e "${YELLOW}🔧 Checking Environment Configuration...${NC}"

    if [[ -f ".env" ]]; then
        echo -e "   ${GREEN}✅ .env file found${NC}"

        # Check required VNPay variables
        if grep -q "VNPAY_TMN_CODE=" ".env"; then
            TMN_CODE=$(grep "VNPAY_TMN_CODE=" ".env" | cut -d'=' -f2-)
            if [[ ! -z "$TMN_CODE" && "$TMN_CODE" != "your_vnpay_terminal_code" ]]; then
                echo -e "   ${GREEN}✅ VNPAY_TMN_CODE configured${NC}"
            else
                echo -e "   ${RED}❌ VNPAY_TMN_CODE not configured${NC}"
            fi
        else
            echo -e "   ${RED}❌ VNPAY_TMN_CODE missing${NC}"
        fi

        if grep -q "VNPAY_HASH_SECRET=" ".env"; then
            HASH_SECRET=$(grep "VNPAY_HASH_SECRET=" ".env" | cut -d'=' -f2-)
            if [[ ! -z "$HASH_SECRET" && "$HASH_SECRET" != "your_vnpay_hash_secret" ]]; then
                echo -e "   ${GREEN}✅ VNPAY_HASH_SECRET configured${NC}"
            else
                echo -e "   ${RED}❌ VNPAY_HASH_SECRET not configured${NC}"
            fi
        else
            echo -e "   ${RED}❌ VNPAY_HASH_SECRET missing${NC}"
        fi

        if grep -q "VNPAY_IPN_URL=" ".env"; then
            echo -e "   ${GREEN}✅ VNPAY_IPN_URL configured${NC}"
        else
            echo -e "   ${YELLOW}⚠️  VNPAY_IPN_URL missing${NC}"
        fi
    else
        echo -e "   ${RED}❌ .env file not found${NC}"
    fi
    echo ""
}

show_help() {
    echo -e "${BLUE}📖 VNPay IPN Helper - Usage:${NC}"
    echo ""
    echo "  $0 [domain]                 - Run all checks for domain (default: localhost)"
    echo "  $0 test [domain]           - Test IPN endpoint only"
    echo "  $0 monitor                 - Monitor IPN logs"
    echo "  $0 ngrok                   - Check ngrok status"
    echo "  $0 env                     - Check environment configuration"
    echo "  $0 config [domain]         - Generate .env configuration"
    echo "  $0 help                    - Show this help"
    echo ""
    echo "Examples:"
    echo "  $0                         - Check localhost"
    echo "  $0 yourdomain.com          - Check production domain"
    echo "  $0 test localhost          - Test localhost IPN"
    echo "  $0 monitor                 - Watch IPN logs"
    echo ""
}

# Main script logic
case "${2:-all}" in
    "test")
        print_header
        print_urls
        test_ipn_endpoint
        ;;
    "monitor")
        print_header
        monitor_logs
        ;;
    "ngrok")
        print_header
        check_ngrok
        ;;
    "env")
        print_header
        check_env_config
        ;;
    "config")
        print_header
        generate_env_config
        ;;
    "help")
        show_help
        ;;
    "all"|*)
        print_header
        print_urls
        check_env_config
        check_ngrok
        test_ipn_endpoint
        monitor_logs
        ;;
esac

case "${1:-help}" in
    "test")
        print_header
        print_urls
        test_ipn_endpoint
        ;;
    "monitor")
        print_header
        monitor_logs
        ;;
    "ngrok")
        print_header
        check_ngrok
        ;;
    "env")
        print_header
        check_env_config
        ;;
    "config")
        print_header
        generate_env_config
        ;;
    "help")
        show_help
        ;;
    *)
        if [[ "$1" != "" && "$1" != "help" ]]; then
            DOMAIN="$1"
            IPN_URL="${PROTOCOL}://${DOMAIN}/payments/vnpay/ipn"
            RETURN_URL="${PROTOCOL}://${DOMAIN}/payments/vnpay/return"

            print_header
            print_urls
            check_env_config
            if [[ "$DOMAIN" == "localhost" ]]; then
                check_ngrok
            fi
            test_ipn_endpoint
            monitor_logs
        else
            show_help
        fi
        ;;
esac
