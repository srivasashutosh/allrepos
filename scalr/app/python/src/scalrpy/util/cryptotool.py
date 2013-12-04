'''
Created on Apr 7, 2010

@author: marat
@edit: roma
'''

from M2Crypto.EVP import Cipher
from M2Crypto.Rand import rand_bytes
import binascii
import hmac
import hashlib
import re
try:
    import timemodule as time
except ImportError:
    import time


'''
crypto_algo = dict(name="des_ede3_cbc", key_size=24, iv_size=8)
crypto_algo = dict(name="des_ede3_cfb", key_size=24, iv_size=8)
'''

def keygen(length=40):
    return binascii.b2a_base64(rand_bytes(length))  


def _init_cipher(crypto_algo, key, op_enc=1):
    skey = key[0:crypto_algo["key_size"]]   # Use first n bytes as crypto key
    iv = key[-crypto_algo["iv_size"]:]      # Use last m bytes as IV
    return Cipher(crypto_algo["name"], skey, iv, op_enc)

        
def encrypt (crypto_algo, s, key):
    c = _init_cipher(crypto_algo, key, 1)
    ret = c.update(s)
    ret += c.final()
    del c
    return binascii.b2a_base64(ret)

    
def decrypt (crypto_algo, s, key):
    c = _init_cipher(crypto_algo, key, 0)
    ret = c.update(binascii.a2b_base64(s))
    ret += c.final()
    del c
    return ret


_READ_BUF_SIZE = 1024 * 1024     # Buffer size in bytes
    

def digest_file(digest, file):
    while 1:
        buf = file.read(_READ_BUF_SIZE)
        if not buf:
            break;
        digest.update(buf)
    return digest.final()


def crypt_file(cipher, in_file, out_file):
    while 1:
        buf = in_file.read(_READ_BUF_SIZE)
        if not buf:
            break
        out_file.write(cipher.update(buf))
    out_file.write(cipher.final())
    

def _get_canonical_string (params={}):
    s = ""
    for key, value in sorted(params.items()):
        s = s + str(key) + str(value)
    return s
        

def sign_http_request(data, key, timestamp=None):
    date = time.strftime("%a %d %b %Y %H:%M:%S %Z", timestamp or time.gmtime())
    canonical_string = _get_canonical_string(data) if hasattr(data, "__iter__") else data
    canonical_string += date
    
    digest = hmac.new(key, canonical_string, hashlib.sha1).digest()
    sign = binascii.b2a_base64(digest)
    if sign.endswith('\n'):
        sign = sign[:-1]
    return sign, date


def pwgen(size):
    return re.sub('[^\w]', '', keygen(size*2))[:size]

