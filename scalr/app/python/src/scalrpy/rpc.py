'''
Created on Nov 21, 2011

@author: marat

JSON-RPC implementation and utilities for 0MQ and HTTP transport 
'''

import sys
import time
try:
    import json
except ImportError:
    import simplejson as json 
from threading import local
import logging


from scalrpy import util


LOG = logging.getLogger(__file__)
LOG.setLevel(logging.DEBUG)
hndlr = logging.StreamHandler()
frmtr= logging.Formatter('%(asctime)s-%(name)s-%(levelname)s# %(message)s')
hndlr.setFormatter(frmtr)
hndlr.setLevel(logging.DEBUG)
LOG.addHandler(hndlr)

def service_method(fn):
    fn._jsonrpc = True
    return fn


class ServiceError(Exception):
    PARSE = -32700
    INVALID_REQUEST = -32600
    METHOD_NOT_FOUND = -32601
    INVALID_PARAMS = -32602
    INTERNAL = -32603
    
    NAMESPACE_NOT_FOUND = -32099
    
    def __init__(self, *args):
        self.code, self.message = args[0:2]
        if len(args) > 2:
            self.data = args[2]
        else:
            self.data = str(sys.exc_info()[1])
        Exception.__init__(self, *args)


class NamespaceNotFoundError(ServiceError):
    def __init__(self, *data):
        ServiceError.__init__(self, self.NAMESPACE_NOT_FOUND, 'Namespace not found', *data)


class MethodNotFoundError(ServiceError):
    def __init__(self, *data):
        ServiceError.__init__(self, self.METHOD_NOT_FOUND, 'Method not found', *data)
        
        
class ParseError(ServiceError):
    def __init__(self, *data):
        ServiceError.__init__(self, self.PARSE, 'Parse error', *data)
        
        
class InvalidRequestError(ServiceError):
    def __init__(self, *data):
        ServiceError.__init__(self, self.INVALID_REQUEST, 'Invalid Request', *data)
        
        
class InvalidParamsError(ServiceError):
    def __init__(self, *data):
        ServiceError.__init__(self, self.INVALID_PARAMS, 'Invalid params', *data)


class InternalError(ServiceError):
    def __init__(self, *data):
        ServiceError.__init__(self, self.INTERNAL, 'Internal error', *data)
    

class RequestHandler(object):
    def __init__(self, services):
        svs = None
        if not services:
            import __main__ as svs
        elif not hasattr(services, '__iter__'):
            svs = services
            
        if svs:
            self.services = {None: svs}
        else:
            self.services = services
        

    def handle_error(self):
        LOG.exception('Caught exception')

        
    def handle_request(self, data, namespace=None):
        id, result, error = '', None, None
        try:
            req = self._parse_request(data) if isinstance(data, basestring) else data 
            id, method, params = self._translate_request(req)
            svs = self._find_service(namespace)
            fn = self._find_method(svs, method)
            result = self._invoke_method(fn, params)
            # important to test json serializarion before serialize the whole result
            json.dumps(result)

        except ServiceError, e:
            error = {'code': e.code, 
                    'message': e.message, 
                    'data': e.data}
        except:
            self.handle_error()
            error = {'code': ServiceError.INTERNAL, 
                    'message': 'Internal error', 
                    'data': str(sys.exc_info()[1])}
        finally:
            if not error:
                resp = {'result': result}
            else:
                resp = {'error': error}
            resp['id'] = id
        return json.dumps(resp)

    
    def _parse_request(self, data):
        try:
            return json.loads(data)
        except:
            raise ParseError()

    
    def _translate_request(self, req):
        try:
            assert req['params'] is not None
            return req['id'], req['method'], req['params']
        except:
            raise InvalidRequestError()


    def _find_service(self, namespace):
        try:
            svs = self.services[namespace]
        except KeyError:
            raise NamespaceNotFoundError(namespace)
        else:
            if isinstance(svs, basestring):
                svs = self.services[namespace] = util.import_object(svs)
            return svs


    def _find_method(self, service, name):
        meth = getattr(service, name, None)
        if meth and getattr(meth, '_jsonrpc', None):
            return meth
        else:
            raise MethodNotFoundError(name)
        
        
    def _invoke_method(self, method, params):
        return method(**params)
        '''
        try:
            return method(**params)
        except:
            raise InternalError()
        '''


class ServiceProxy(object):

    def __init__(self):
        self.local = local()


    def __getattr__(self, name):
        try:
            self.__dict__['local'].method.append(name)
        except AttributeError:
            self.__dict__['local'].method = [name]
        return self
    

    def __call__(self, timeout=None, **kwds):
        try:
            req = json.dumps({'method': self.local.method[-1], 'params': kwds, 'id': time.time()})
            resp = json.loads(self.exchange(req, timeout=timeout))
            if 'error' in resp:
                error = resp['error']
                raise ServiceError(error.get('code'), error.get('message'), error.get('data'))
            return resp['result']
        finally:
            self.local.method = []
        

    def exchange(self, request):
        raise NotImplementedError()
    
    
class Server(object):

    def __init__(self, endpoint, handler):
        raise NotImplementedError()


    def serve_forever(self):
        raise NotImplementedError()
    

    def stop(self):
        raise NotImplementedError()

