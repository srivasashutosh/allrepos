'''
JSON-RPC over HTTP. 

Public Scalarizr API

- Simple to Learn
- Simple to Use
'''
from __future__ import with_statement

import os
import binascii
import sys
import time
import urllib2
import hashlib
import hmac
try:
    import json
except ImportError:
    import simplejson as json


from scalrpy import rpc
from scalrpy.util import cryptotool


class Security(object):
    DATE_FORMAT = "%a %d %b %Y %H:%M:%S UTC"
    
    def __init__(self, crypto_key):
        self._crypto_key = binascii.a2b_base64(crypto_key)


    def _read_crypto_key(self, crypto_key_path):
        return binascii.a2b_base64(open(crypto_key_path).read().strip())
    

    def sign(self, data, key, timestamp=None):
        date = time.strftime(self.DATE_FORMAT, timestamp or time.gmtime())
        canonical_string = data + date
        
        digest = hmac.new(key, canonical_string, hashlib.sha1).digest()
        sign = binascii.b2a_base64(digest)
        if sign.endswith('\n'):
            sign = sign[:-1]
        return sign, date
    
    
    def check_signature(self, signature, data, timestamp):
        calc_signature = self.sign(data, self._crypto_key, 
                                time.strptime(timestamp, self.DATE_FORMAT))[0]
        assert signature == calc_signature, "Signature doesn't match"
    
    
    def decrypt_data(self, data):
        try:
            crypto_algo = dict(name="des_ede3_cbc", key_size=24, iv_size=8)
            return cryptotool.decrypt(crypto_algo, data, self._crypto_key)
        except:
            raise rpc.InvalidRequestError('Failed to decrypt data')


    def encrypt_data(self, data):
        try:
            crypto_algo = dict(name="des_ede3_cbc", key_size=24, iv_size=8)
            return cryptotool.encrypt(crypto_algo, data, self._crypto_key)
        except:
            raise rpc.InvalidRequestError('Failed to encrypt data. Error: %s' % (sys.exc_info()[1], ))


class HttpServiceProxy(rpc.ServiceProxy, Security):

    def __init__(self, endpoint, _crypto_key):
        Security.__init__(self, _crypto_key)
        rpc.ServiceProxy.__init__(self)
        self.endpoint = endpoint

    
    def exchange(self, jsonrpc_req, timeout=None):
        jsonrpc_req = self.encrypt_data(jsonrpc_req)
        sig, date = self.sign(jsonrpc_req, self._crypto_key)
        
        headers = {
            'Date': date,
            'X-Signature': sig
        }
        namespace = self.local.method[0] if len(self.local.method) > 1 else ''
        
        http_req = urllib2.Request(os.path.join(self.endpoint, namespace), jsonrpc_req, headers)
        try:
            jsonrpc_resp = urllib2.urlopen(http_req, timeout=timeout).read()
            return self.decrypt_data(jsonrpc_resp)
        except urllib2.HTTPError, e:
            raise Exception('%s: %s' % (e.code, e.read()))

