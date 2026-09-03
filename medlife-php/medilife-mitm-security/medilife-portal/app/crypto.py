"""
MediLife Portal - Cryptographic Utilities

AES-256-GCM encryption for sensitive patient data.
"""

import os
import base64
import hashlib
from typing import Tuple

from cryptography.hazmat.primitives.ciphers.aead import AESGCM


# Global encryption key (initialized at app startup)
_encryption_key = None


def init_encryption(app_data_key: str):
    """
    Initialize the encryption module with the application data key.

    Args:
        app_data_key: The encryption key from environment configuration
    """
    global _encryption_key
    # Derive a 32-byte key from the provided key using SHA-256
    _encryption_key = hashlib.sha256(app_data_key.encode('utf-8')).digest()


def get_encryption_key() -> bytes:
    """
    Get the current encryption key.

    Returns:
        bytes: 32-byte AES key

    Raises:
        RuntimeError: If encryption has not been initialized
    """
    if _encryption_key is None:
        raise RuntimeError("Encryption not initialized. Call init_encryption() first.")
    return _encryption_key


def encrypt_field(plaintext: str) -> Tuple[bytes, bytes]:
    """
    Encrypt a string value using AES-256-GCM.

    Args:
        plaintext: The string to encrypt

    Returns:
        Tuple of (ciphertext, nonce) as bytes

    Raises:
        RuntimeError: If encryption has not been initialized
    """
    key = get_encryption_key()
    aesgcm = AESGCM(key)

    # Generate a random 96-bit nonce
    nonce = os.urandom(12)

    # Encrypt the data
    ciphertext = aesgcm.encrypt(nonce, plaintext.encode('utf-8'), None)

    return ciphertext, nonce


def decrypt_field(ciphertext: bytes, nonce: bytes) -> str:
    """
    Decrypt a value using AES-256-GCM.

    Args:
        ciphertext: The encrypted data
        nonce: The nonce used for encryption

    Returns:
        Decrypted string

    Raises:
        RuntimeError: If encryption has not been initialized
        cryptography.exceptions.InvalidTag: If decryption fails (tampered data)
    """
    key = get_encryption_key()
    aesgcm = AESGCM(key)

    # Decrypt the data
    plaintext = aesgcm.decrypt(nonce, ciphertext, None)

    return plaintext.decode('utf-8')


def generate_secure_token(length: int = 32) -> str:
    """
    Generate a cryptographically secure random token.

    Args:
        length: Number of random bytes to generate

    Returns:
        URL-safe base64 encoded string
    """
    return base64.urlsafe_b64encode(os.urandom(length)).decode('ascii').rstrip('=')


def hash_session_id(session_id: str) -> str:
    """
    Create a secure hash of a session ID for storage.

    Args:
        session_id: The raw session ID

    Returns:
        SHA-256 hash of the session ID (hex string)
    """
    return hashlib.sha256(session_id.encode('utf-8')).hexdigest()


def verify_encryption_health() -> dict:
    """
    Verify that encryption is working correctly.

    Returns:
        Dictionary with health check results
    """
    try:
        test_data = "MediLife encryption test"
        ciphertext, nonce = encrypt_field(test_data)
        decrypted = decrypt_field(ciphertext, nonce)

        return {
            'status': 'healthy',
            'encryption_working': test_data == decrypted,
            'key_configured': _encryption_key is not None
        }
    except Exception as e:
        return {
            'status': 'unhealthy',
            'error': str(e),
            'key_configured': _encryption_key is not None
        }
