#!/usr/bin/env python3
"""
Script específico para Windows para ejecutar tests
"""

import subprocess
import sys
import os
from pathlib import Path

def main():
    """Función principal"""
    print("🧪 SISTEMA DE TESTING - CAFÉ-VT LOYALTY (WINDOWS)")
    print("=" * 60)
    
    # Cambiar al directorio del proyecto
    project_dir = Path(__file__).parent
    os.chdir(project_dir)
    
    print(f"📁 Directorio: {os.getcwd()}")
    
    # Rutas posibles de Python en Windows
    python_paths = [
        "C:/Users/PC/AppData/Local/Programs/Python/Python312/python.exe",
        "C:/Python312/python.exe",
        "C:/Python311/python.exe",
        "C:/Python310/python.exe",
        "C:/Users/PC/miniconda3/python.exe",
        "C:/Users/PC/anaconda3/python.exe",
        "py",  # Launcher de Python
        sys.executable  # El Python que está ejecutando este script
    ]
    
    # Encontrar Python que funcione
    python_cmd = None
    for path in python_paths:
        try:
            print(f"🔍 Probando: {path}")
            result = subprocess.run([path, "--version"], capture_output=True, text=True)
            if result.returncode == 0:
                print(f"✅ Python encontrado: {path}")
                print(f"   Versión: {result.stdout.strip()}")
                python_cmd = path
                break
        except Exception as e:
            print(f"   ❌ No funciona: {e}")
            continue
    
    if not python_cmd:
        print("❌ No se pudo encontrar Python. Instala Python desde python.org")
        return 1
    
    # Verificar pytest
    print(f"\n🔍 Verificando pytest...")
    try:
        result = subprocess.run([python_cmd, "-m", "pytest", "--version"], capture_output=True, text=True)
        if result.returncode == 0:
            print(f"✅ pytest funciona: {result.stdout.strip()}")
        else:
            print("❌ pytest no funciona, instalando...")
            subprocess.run([python_cmd, "-m", "pip", "install", "pytest", "pytest-asyncio", "pytest-cov", "pytest-html"], check=True)
    except Exception as e:
        print(f"❌ Error con pytest: {e}")
        return 1
    
    # Crear directorio de reportes
    Path("reports").mkdir(exist_ok=True)
    
    # Ejecutar test simple primero
    print(f"\n🧪 Ejecutando test simple...")
    try:
        result = subprocess.run([python_cmd, "-m", "pytest", "test_simple.py", "-v"], 
                              capture_output=True, text=True)
        if result.returncode == 0:
            print("✅ Test simple pasó")
            print(result.stdout)
        else:
            print("❌ Test simple falló")
            print(result.stderr)
    except Exception as e:
        print(f"❌ Error ejecutando test: {e}")
    
    # Ejecutar tests unitarios
    print(f"\n🔬 Ejecutando tests unitarios...")
    try:
        result = subprocess.run([python_cmd, "-m", "pytest", "tests/", "-m", "unit", "-v"], 
                              capture_output=True, text=True)
        if result.returncode == 0:
            print("✅ Tests unitarios pasaron")
            print(result.stdout)
        else:
            print("❌ Tests unitarios fallaron")
            print(result.stderr)
    except Exception as e:
        print(f"❌ Error ejecutando tests unitarios: {e}")
    
    # Ejecutar todos los tests
    print(f"\n🎯 Ejecutando todos los tests...")
    try:
        result = subprocess.run([python_cmd, "-m", "pytest", "tests/", "-v"], 
                              capture_output=True, text=True)
        if result.returncode == 0:
            print("✅ Todos los tests pasaron")
            print(result.stdout)
        else:
            print("❌ Algunos tests fallaron")
            print(result.stderr)
    except Exception as e:
        print(f"❌ Error ejecutando tests: {e}")
    
    # Generar reporte de cobertura
    print(f"\n📊 Generando reporte de cobertura...")
    try:
        result = subprocess.run([python_cmd, "-m", "pytest", "tests/", "--cov=services", "--cov=utils", "--cov-report=html"], 
                              capture_output=True, text=True)
        if result.returncode == 0:
            print("✅ Reporte de cobertura generado")
            print("📁 Ver reporte en: htmlcov/index.html")
        else:
            print("❌ Error generando reporte de cobertura")
    except Exception as e:
        print(f"❌ Error generando reporte: {e}")
    
    print("\n🎉 ¡Proceso completado!")
    return 0

if __name__ == "__main__":
    sys.exit(main()) 