"""
Rutas del sistema de fidelización Café-VT
"""

__version__ = "1.0.0"
__author__ = "Café-VT Development Team"

from .loyalty_routes import router as loyalty_router

# Incluir todas las rutas
__all__ = ['loyalty_router'] 