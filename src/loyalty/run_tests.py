#!/usr/bin/env python3
"""
Script para ejecutar todos los tests del sistema de fidelización
"""

import subprocess
import sys
import os
from pathlib import Path

def run_command(command, description):
    """Ejecutar comando y mostrar resultado"""
    print(f"\n{'='*60}")
    print(f"🚀 {description}")
    print(f"{'='*60}")
    
    try:
        # Usar python.exe en lugar de python
        if command.startswith("python -m"):
            command = command.replace("python -m", "python.exe -m")
        elif command.startswith("pip install"):
            command = command.replace("pip install", "python.exe -m pip install")
        
        result = subprocess.run(command, shell=True, check=True, capture_output=True, text=True)
        print("✅ Comando ejecutado exitosamente")
        print(result.stdout)
        return True
    except subprocess.CalledProcessError as e:
        print("❌ Error ejecutando comando")
        print(f"Error: {e.stderr}")
        return False

def main():
    """Función principal"""
    print("🧪 SISTEMA DE TESTING - CAFÉ-VT LOYALTY")
    print("=" * 60)
    
    # Cambiar al directorio del proyecto
    project_dir = Path(__file__).parent
    os.chdir(project_dir)
    
    # Verificar que estamos en el directorio correcto
    if not Path("tests").exists():
        print("❌ Error: No se encontró el directorio 'tests'")
        print("Asegúrate de ejecutar este script desde el directorio del proyecto")
        sys.exit(1)
    
    # 1. Instalar dependencias de testing
    print("\n📦 Instalando dependencias de testing...")
    dependencies = [
        "pytest",
        "pytest-asyncio", 
        "pytest-cov",
        "pytest-html",
        "pytest-xdist"
    ]
    
    for dep in dependencies:
        run_command(f"python.exe -m pip install {dep}", f"Instalando {dep}")
    
    # 2. Ejecutar tests unitarios
    print("\n🔬 Ejecutando tests unitarios...")
    unit_success = run_command(
        "python.exe -m pytest tests/ -m unit -v --tb=short",
        "Tests Unitarios"
    )
    
    # 3. Ejecutar tests de integración
    print("\n🔗 Ejecutando tests de integración...")
    integration_success = run_command(
        "python.exe -m pytest tests/ -m integration -v --tb=short",
        "Tests de Integración"
    )
    
    # 4. Ejecutar tests de API
    print("\n🌐 Ejecutando tests de API...")
    api_success = run_command(
        "python.exe -m pytest tests/ -m api -v --tb=short",
        "Tests de API"
    )
    
    # 5. Ejecutar tests de base de datos
    print("\n🗄️ Ejecutando tests de base de datos...")
    db_success = run_command(
        "python.exe -m pytest tests/ -m database -v --tb=short",
        "Tests de Base de Datos"
    )
    
    # 6. Ejecutar tests de usuario
    print("\n👤 Ejecutando tests de usuario...")
    user_success = run_command(
        "python.exe -m pytest tests/ -m user -v --tb=short",
        "Tests de Usuario"
    )
    
    # 7. Ejecutar tests de rendimiento
    print("\n⚡ Ejecutando tests de rendimiento...")
    performance_success = run_command(
        "python.exe -m pytest tests/ -m performance -v --tb=short",
        "Tests de Rendimiento"
    )
    
    # 8. Generar reporte de cobertura
    print("\n📊 Generando reporte de cobertura...")
    coverage_success = run_command(
        "python.exe -m pytest tests/ --cov=services --cov=utils --cov-report=html --cov-report=term-missing",
        "Reporte de Cobertura"
    )
    
    # 9. Generar reporte HTML
    print("\n📄 Generando reporte HTML...")
    html_success = run_command(
        "python.exe -m pytest tests/ --html=reports/test_report.html --self-contained-html",
        "Reporte HTML"
    )
    
    # 10. Ejecutar todos los tests
    print("\n🎯 Ejecutando todos los tests...")
    all_tests_success = run_command(
        "python.exe -m pytest tests/ -v --tb=short",
        "Todos los Tests"
    )
    
    # Resumen final
    print("\n" + "="*60)
    print("📋 RESUMEN DE TESTS")
    print("="*60)
    
    results = {
        "Unitarios": unit_success,
        "Integración": integration_success,
        "API": api_success,
        "Base de Datos": db_success,
        "Usuario": user_success,
        "Rendimiento": performance_success,
        "Cobertura": coverage_success,
        "Reporte HTML": html_success,
        "Todos los Tests": all_tests_success
    }
    
    passed = sum(results.values())
    total = len(results)
    
    for test_type, success in results.items():
        status = "✅ PASÓ" if success else "❌ FALLÓ"
        print(f"{test_type:20} {status}")
    
    print(f"\n📈 Resultado: {passed}/{total} categorías pasaron")
    
    if passed == total:
        print("🎉 ¡Todos los tests pasaron exitosamente!")
        return 0
    else:
        print("⚠️  Algunos tests fallaron. Revisa los logs arriba.")
        return 1

if __name__ == "__main__":
    sys.exit(main()) 