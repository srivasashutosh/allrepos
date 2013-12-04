#!/bin/bash

D=${PWD}

PYTHON=python2.6

yum install -y python-devel
yum install -y python26-devel

$PYTHON -c 'import setuptools'
if [ $? -ne 0 ]; then
    echo "INSATLLING setuptools"
    cd /tmp
    wget https://bitbucket.org/pypa/setuptools/raw/0.8/ez_setup.py --no-check-certificate
    $PYTHON ez_setup.py
    rm ez_setup.py
fi

$PYTHON -c 'import netsnmp'
if [ $? -ne 0 ]; then
    cd /tmp
    echo "DOWNLOADING netsnmp"
    wget http://sourceforge.net/projects/net-snmp/files/net-snmp/5.7.2/net-snmp-5.7.2.tar.gz --no-check-certificate
    echo "UNTARING netsnmp"
    tar -xvf net-snmp-5.7.2.tar.gz 1>/dev/null
    cd net-snmp-5.7.2
    echo "CONFIGURING"
    ./configure --prefix=/usr --with-python-modules --libdir=/usr/lib64
    echo "MAKING"
    make 1>/dev/null
    echo "INSTALLING"
    make install 1>/dev/null
    echo "SETUP.PY INSTALL"
    cd python
    $PYTHON setup.py install
    cd ..
    echo "INSTALLING"
    make install 1>/dev/null
    cd /tmp
    rm net-snmp-5.7.2* -rf
fi

$PYTHON -c 'import rrdtool'
if [ $? -ne 0 ]; then
    cd /tmp
    yum install -y pango-devel
    yum install -y cairo-devel
    yum install -y libxml2-devel
    echo "DOWNLOADING rrdtool"
    wget http://oss.oetiker.ch/rrdtool/pub/rrdtool-1.4.8.tar.gz --no-check-certificate
    echo "UNTARING netsnmp"
    tar -xvf rrdtool-1.4.8.tar.gz 1>/dev/null
    cd rrdtool-1.4.8

    echo "CONFIGURING"
    ./configure --prefix=/usr
    echo "MAKING"
    make 1>/dev/null
    echo "INSTALLING"
    make install 1>/dev/null

    make distclean

    echo "CONFIGURING"
    ./configure --prefix=/usr --libdir=/usr/lib64
    echo "MAKING"
    make 1>/dev/null
    echo "INSTALLING"
    make install 1>/dev/null

    echo "SETUP.PY INSTALL"
    cd bindings/python
    $PYTHON setup.py install
    cd /tmp
    rm rrdtool-1.4.8* -rf
fi

#echo "INSTALLING ScalrPy"
#cd $D
#cd $0
#$PYTHON setup.py install

