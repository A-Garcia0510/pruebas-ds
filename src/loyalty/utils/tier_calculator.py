from typing import Dict

def calculate_tier_benefits(tier: str) -> Dict[str, str]:
    """Devuelve los beneficios asociados a un nivel de fidelización."""
    benefits = {
        "cafe_bronze": {"descuento": "5%", "mensaje": "Bienvenido a Café Bronze"},
        "cafe_plata": {"descuento": "10%", "mensaje": "¡Eres Café Plata!"},
        "cafe_oro": {"descuento": "15%", "mensaje": "¡Nivel Oro alcanzado!"},
        "cafe_diamante": {"descuento": "20%", "mensaje": "¡Eres un cliente Diamante!"},
    }
    return benefits.get(tier, {"descuento": "0%", "mensaje": "Sin beneficios"}) 