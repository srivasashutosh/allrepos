
import sys
import platform
import subprocess as subps

pkg_manager = None
system_requires = []

if platform.dist()[0] in ['debian', 'Ubuntu']:
    pkg_manager = 'apt-get'
    system_requires.append('python-m2crypto')
    system_requires.append('libyaml-dev')
    system_requires.append('swig')
    system_requires.append('python-dev')
    system_requires.append('libevent-dev')
    system_requires.append('libsnmp-python')
    system_requires.append('python-rrdtool')
elif platform.dist()[0] in ['redhat', 'centos']:
    pkg_manager = 'yum'
    system_requires = []
    system_requires.append('python-devel')
    system_requires.append('swig')
    system_requires.append('libevent-devel')
    if platform.linux_distribution()[0] == 'CentOS' and platform.linux_distribution()[1] < '6':
        system_requires.append('python26-m2crypto')
    else:
        system_requires.append('m2crypto')
        system_requires.append('net-snmp-python')
        system_requires.append('rrdtool-python')

if not pkg_manager:
    sys.stderr.write('Unsupported platform\n')
    sys.exit(1)

try:
    from setuptools import setup, find_packages
except:
    system_requires.append('python-setuptools')


if pkg_manager:
    cmd = '%s install -y' % pkg_manager
    for pkg in system_requires:
        try:
            subps.call(cmd.split() + [pkg])
        except:
            print sys.exc_info()

requires = ['pyaml', 'gevent', 'sqlalchemy', 'sqlsoup', 'pymysql', 'psutil']
if sys.version_info < (2, 7):
    requires.append(['argparse'])


from setuptools import setup, find_packages

setup(
    name = 'ScalrPy',
    version = open('src/scalrpy/version').read().strip(),
    author = "Scalr Inc.",
    author_email = "info@scalr.net",
    url = "https://scalr.net",
    license = 'ASL 2.0',
    description = ('Set of python scripts for Scalr'),
    package_dir = {'':'src'},
    packages = find_packages('src'),
    include_package_data = True,
    install_requires = requires,
)

