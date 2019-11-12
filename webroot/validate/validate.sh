#!/bin/bash

make -q

wget -q -c "https://nia.otevrenamesta.cz/SeP/Konfigurace.xml"

env XML_CATALOG_FILES=xcatalog/saml-metadata.xml xmllint --schema saml-2.0-os/saml-schema-metadata-2.0.xsd --noout Konfigurace.xml
