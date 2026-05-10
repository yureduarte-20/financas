#!/bin/bash

# Script para execução de testes do FinançasPessoais
# Uso: ./run-tests.sh [opção]

set -e

echo "========================================"
echo "  FinançasPessoais - Test Suite"
echo "========================================"
echo ""

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para executar testes unitários
run_unit_tests() {
    echo -e "${YELLOW}Executando testes unitários...${NC}"
    ./vendor/bin/phpunit --testsuite Unit --testdox
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Testes unitários passaram!${NC}"
    else
        echo -e "${RED}✗ Testes unitários falharam!${NC}"
        exit 1
    fi
    echo ""
}

# Função para executar testes de feature
run_feature_tests() {
    echo -e "${YELLOW}Executando testes de feature...${NC}"
    ./vendor/bin/phpunit --testsuite Feature --testdox
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Testes de feature passaram!${NC}"
    else
        echo -e "${RED}✗ Testes de feature falharam!${NC}"
        exit 1
    fi
    echo ""
}

# Função para executar testes com cobertura
run_tests_with_coverage() {
    echo -e "${YELLOW}Executando testes com cobertura...${NC}"
    ./vendor/bin/phpunit --coverage-html coverage-html --coverage-text --min-coverage=60
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Testes com cobertura passaram!${NC}"
        echo -e "${GREEN}Relatório de cobertura gerado em: coverage-html/index.html${NC}"
    else
        echo -e "${RED}✗ Testes com cobertura falharam!${NC}"
        exit 1
    fi
    echo ""
}

# Função para executar todos os testes
run_all_tests() {
    echo -e "${YELLOW}Executando todos os testes...${NC}"
    ./vendor/bin/phpunit --testdox
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Todos os testes passaram!${NC}"
    else
        echo -e "${RED}✗ Alguns testes falharam!${NC}"
        exit 1
    fi
    echo ""
}

# Menu principal
case "${1:-all}" in
    unit)
        run_unit_tests
        ;;
    feature)
        run_feature_tests
        ;;
    coverage)
        run_tests_with_coverage
        ;;
    all|*)
        run_all_tests
        ;;
esac

echo "========================================"
echo -e "${GREEN}Testes concluídos com sucesso!${NC}"
echo "========================================"
