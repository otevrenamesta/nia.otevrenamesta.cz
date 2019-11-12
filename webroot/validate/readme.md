# Validace XML schématu SAML2

Použití pro Linux/Unix systémy

```
// stáhne a zkompiluje XSD katalogy pro validaci saml2 entit
// pokud již existuje soubor "xcatalog/saml-metadata.xml" tak se nic nestane
make
// validuje daný xml soubor xsd katalogem
env XML_CATALOG_FILES=xcatalog/saml-metadata.xml xmllint --schema saml-2.0-os/saml-schema-metadata-2.0.xsd --noout ValidovanySoubor.xml
```

Potřebné instalované nástroje:

  - make
  - wget
  - unzip
  - xmllint (libxml2-utils)
  - xmlcatalog (libxml2-utils)
