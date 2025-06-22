import secrets
import string

def generate_unique_code(length: int = 8) -> str:
    """Genera un código único alfanumérico de la longitud especificada."""
    alphabet = string.ascii_uppercase + string.digits
    return ''.join(secrets.choice(alphabet) for _ in range(length)) 