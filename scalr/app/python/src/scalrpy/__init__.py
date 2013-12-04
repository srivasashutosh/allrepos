
import os
import sys
import pkgutil

__version__ = open(os.path.join(os.path.dirname(__file__), 'version')).read().strip()


class NotFound(Exception):
	pass


def import_class(import_str):
	"""Returns a class from a string including module and class"""

	mod_str, _sep, class_str = import_str.rpartition('.')
	try:
		loader = pkgutil.find_loader(mod_str)
		if not loader:
			raise ImportError('No module named %s' % mod_str)
	except ImportError:
		pass
	else:
		loader.load_module('')
		try:
			return getattr(sys.modules[mod_str], class_str)
		except (ValueError, AttributeError):
			pass
	raise NotFound('Class %s cannot be found' % import_str)


def import_object(import_str, *args, **kwds):
	"""Returns an object including a module or module and class"""

	try:
		__import__(import_str)
		return sys.modules[import_str]
	except ImportError:
		cls = import_class(import_str)
		return cls(*args, **kwds)

