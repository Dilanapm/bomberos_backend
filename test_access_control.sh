#!/bin/bash

echo "========================================"
echo "TEST EXHAUSTIVO DE CONTROL DE ACCESO"
echo "Solo ADMIN debe tener acceso"
echo "========================================"
echo ""

# Colores
RED='\033[0:31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8081"

# Función para hacer login y obtener cookie + CSRF
login_user() {
    local email=$1
    local password=$2
    local cookie_file=$(mktemp)
    
    # Obtener CSRF
    CSRF=$(curl -s -c "$cookie_file" "$BASE_URL/login" | grep -oP '(?<=name="_token" value=")[^"]+' | head -1)
    
    # Hacer login
    curl -s -b "$cookie_file" -c "$cookie_file" -X POST "$BASE_URL/login" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        --data "_token=$CSRF&email=$email&password=$password" \
        -L -o /dev/null
    
    echo "$cookie_file"
}

# Función para probar acceso a una ruta
test_access() {
    local role=$1
    local cookie_file=$2
    local url=$3
    local route_name=$4
    
    response=$(curl -s -b "$cookie_file" "$url" -w "\n%{http_code}" -L)
    status_code=$(echo "$response" | tail -1)
    body=$(echo "$response" | head -n -1)
    
    if [ "$role" == "admin" ]; then
        # Admin DEBE tener acceso (200 o 302 a zona admin)
        if [ "$status_code" == "200" ] || ([ "$status_code" == "302" ] && echo "$body" | grep -q "admin"); then
            echo -e "${GREEN}✓${NC} $role → $route_name: Acceso permitido (HTTP $status_code)"
        else
            echo -e "${RED}✗ FALLO${NC} $role → $route_name: Debería tener acceso pero obtuvo HTTP $status_code"
        fi
    else
        # Instructor/Aprendiz NO deben tener acceso
        if [ "$status_code" == "403" ] || [ "$status_code" == "302" ] || [ "$status_code" == "401" ]; then
            echo -e "${GREEN}✓${NC} $role → $route_name: Acceso BLOQUEADO correctamente (HTTP $status_code)"
        elif [ "$status_code" == "200" ] && echo "$body" | grep -q "no tiene acceso"; then
            echo -e "${GREEN}✓${NC} $role → $route_name: Acceso BLOQUEADO con mensaje"
        elif [ "$status_code" == "200" ]; then
            echo -e "${RED}✗ VULNERABILIDAD${NC} $role → $route_name: Tiene acceso cuando NO debería (HTTP $status_code)"
        else
            echo -e "${YELLOW}?${NC} $role → $route_name: Respuesta inesperada (HTTP $status_code)"
        fi
    fi
}

echo "=== Paso 1: Probando acceso con ADMIN ==="
echo ""
admin_cookie=$(login_user "admin@bomberos.local" "password123")
test_access "admin" "$admin_cookie" "$BASE_URL/admin/zone" "admin/zone"
test_access "admin" "$admin_cookie" "$BASE_URL/dashboard" "dashboard"
echo ""

echo "=== Paso 2: Probando acceso con INSTRUCTOR (DEBE SER BLOQUEADO) ==="
echo ""
instructor_cookie=$(login_user "instructor@bomberos.local" "password123")
test_access "instructor" "$instructor_cookie" "$BASE_URL/admin/zone" "admin/zone"
test_access "instructor" "$instructor_cookie" "$BASE_URL/admin/passkeys-ui" "admin/passkeys-ui"
test_access "instructor" "$instructor_cookie" "$BASE_URL/dashboard" "dashboard"
echo ""

echo "=== Paso 3: Probando acceso con APRENDIZ (DEBE SER BLOQUEADO) ==="
echo ""
aprendiz_cookie=$(login_user "aprendiz@bomberos.local" "password123")
test_access "aprendiz" "$aprendiz_cookie" "$BASE_URL/admin/zone" "admin/zone"
test_access "aprendiz" "$aprendiz_cookie" "$BASE_URL/admin/passkeys-ui" "admin/passkeys-ui"
test_access "aprendiz" "$aprendiz_cookie" "$BASE_URL/dashboard" "dashboard"
echo ""

# Limpiar cookies temporales
rm -f "$admin_cookie" "$instructor_cookie" "$aprendiz_cookie"

echo "========================================"
echo "TEST COMPLETADO"
echo "========================================"
