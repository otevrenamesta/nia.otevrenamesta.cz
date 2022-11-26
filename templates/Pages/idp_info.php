<?php
/**
 * @var $this AppView
 * @var $title string
 */

use App\View\AppView; ?>
<h1><?= $title ?></h1>

<p>NIA (poskytována organizací SZRČR) je ve smyslu SAML služeb IdP, tedy poskytovatel identit.

<p>Pro integraci lze využít následující standardy komunikace

<ol>
    <li>
        <a target="_blank" href="https://cs.wikipedia.org/wiki/Security_Assertion_Markup_Language"
           title="Security Assertion Markup Language - Wikipedia">SAML2 Core</a> /
        <a target="_blank" title="electronic IDentification, Authentication and trust Services - Wikipedia"
           href="https://cs.wikipedia.org/wiki/EIDAS">eIDAS</a>
    </li>
    <li>
        <a title="Web Services Federation Language - Wikipedia" target="_blank"
           href="https://en.wikipedia.org/wiki/WS-Federation">WS-Federation</a>
    </li>
</ol>

<p>Abychom se mohli integrovat s IdP, je třeba pochopit poskytované služby a rozhraní, k tomu slouží IdP metadata.
<p>Dle <a href="https://info.identitaobcana.cz/download/SeP_PriruckaKvalifikovanehoPoskytovatele.pdf">příručky
        SeP</a> jsou metadata na těchto adresách

<ol>
    <li>Testovací prostředí: <a target="_blank"
                                href="https://tnia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml">https://tnia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml</a>
    </li>
    <li>Produkční prostředí: <a target="_blank"
                                href="https://nia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml">https://nia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml</a>
    </li>
</ol>

<h2>Ukázka XML metadat (testovací prostředí)</h2>
<pre><code class="xml">&lt;?xml version="1.0"?>
&lt;!-- Sekce s podpisem a kontrolním součtem obsahu dokumentu (rsa-sha256) dle specifikace xmldsig --&gt;

&lt;EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" ID="_b931baf7-9318-49cc-be37-d347ecf24a44" entityID="urn:microsoft:cgg2010:fpsts">
  &lt;Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
    &lt;SignedInfo>
      &lt;CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
      &lt;SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
      &lt;Reference URI="#_b931baf7-9318-49cc-be37-d347ecf24a44">
        &lt;Transforms>
          &lt;Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
          &lt;Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
        &lt;/Transforms>
        &lt;DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
        &lt;DigestValue>C3CAUoTaZ59+kAthzBU2Puz65hrvzwcINxHxrBOuhwk=&lt;/DigestValue>
      &lt;/Reference>
    &lt;/SignedInfo>
    &lt;SignatureValue>ElTkmNlB0ybN4LRT33Vf/FO57WxuQHLA8tPg0tuMUcx3NkTUGQ2IkcQS+8raGkp2ExTXeywUAolerK5h9cbKclyjRB6d01zE5QbynRP1kVVsIapRDO2G22jljFwTXyJrQs1jtgoZPN/6flhVlG77JnBNjy/EZKY8swHfwTpniqqfwinfd6vvoyMLxUkKg4b8Aif6wHIjaYyanEQAvNZeWfqAB2yIo15gJmdK3y5jdZCoODm5lfeHnVx3dRgZQZo6M+zVS5UmPJAPX7W8cAZSso6JGu2YX3pC/TpcOnk6PXYVW9XXtpecmnIqTpSriDM8wnyw9EEkDQPMdMl7exygag==&lt;/SignatureValue>
    &lt;KeyInfo>
      &lt;X509Data>
        &lt;X509Certificate>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&lt;/X509Certificate>
      &lt;/X509Data>
    &lt;/KeyInfo>
  &lt;/Signature>

 &lt;!-- Metadata pro kofiguraci protistrany komunikující protokolem WS-Federation --&gt;

  &lt;RoleDescriptor xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:fed="http://docs.oasis-open.org/wsfed/federation/200706" xsi:type="fed:SecurityTokenServiceType" protocolSupportEnumeration="http://docs.oasis-open.org/wsfed/federation/200706">
    &lt;KeyDescriptor use="signing">
      &lt;KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
        &lt;X509Data>
          &lt;X509Certificate>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&lt;/X509Certificate>
        &lt;/X509Data>
      &lt;/KeyInfo>
    &lt;/KeyDescriptor>
    &lt;fed:TokenTypesOffered>
      &lt;fed:TokenType Uri="http://schemas.microsoft.com/ws/2006/05/identitymodel/tokens/Saml"/>
    &lt;/fed:TokenTypesOffered>
    &lt;fed:ClaimTypesOffered>
      &lt;auth:ClaimType xmlns:auth="http://docs.oasis-open.org/wsfed/authorization/200706" Uri="http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress">
        &lt;auth:DisplayName>Email address&lt;/auth:DisplayName>
        &lt;auth:Description>The email of the subject.&lt;/auth:Description>
      &lt;/auth:ClaimType>
    &lt;/fed:ClaimTypesOffered>
    &lt;fed:SecurityTokenServiceEndpoint>
      &lt;wsa:EndpointReference xmlns:wsa="http://www.w3.org/2005/08/addressing">
        &lt;wsa:Address>https://tnia.identitaobcana.cz/FPSTS/issue.svc&lt;/wsa:Address>
      &lt;/wsa:EndpointReference>
    &lt;/fed:SecurityTokenServiceEndpoint>
    &lt;fed:PassiveRequestorEndpoint>
      &lt;wsa:EndpointReference xmlns:wsa="http://www.w3.org/2005/08/addressing">
        &lt;wsa:Address>https://tnia.identitaobcana.cz/FPSTS/default.aspx&lt;/wsa:Address>
      &lt;/wsa:EndpointReference>
    &lt;/fed:PassiveRequestorEndpoint>
  &lt;/RoleDescriptor>

 &lt;!-- Metadata pro konfiguraci protistrany komunikující protokolem SAML2.0 dle eIDAS --&gt;

  &lt;IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    &lt;KeyDescriptor use="signing">
      &lt;KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
        &lt;X509Data>
          &lt;X509Certificate>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&lt;/X509Certificate>
        &lt;/X509Data>
      &lt;/KeyInfo>
    &lt;/KeyDescriptor>
    &lt;SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://tnia.identitaobcana.cz/FPSTS/saml2/basic"/>
    &lt;SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://tnia.identitaobcana.cz/FPSTS/saml2/basic"/>
    &lt;SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="https://tnia.identitaobcana.cz/FPSTS/saml2/basic"/>
  &lt;/IDPSSODescriptor>
&lt;/EntityDescriptor>

</code></pre>

<h2>Popis metadat</h2>
<ul>
    <li>Metadata obsahují konfiguraci jak pro klienta podporujícího specifikaci SAML2 Core, tak pro WS-Federation</li>
    <li><code style="background-color: #eee">/RoleDescriptor</code> je popisem služeb pro WS-Federation</li>
    <li><code style="background-color: #eee">/IDPSSODescriptor</code> je popisem služeb pro SAML2</li>
    <li><code style="background-color: #eee">/Signature</code> obsahuje SHA256 hash a podpis, příslušným certifikátem,
        celého dokumentu (dle <code style="background-color: #eee">/Signature/SignedInfo/Reference@URI</code>)
    </li>
    <li><code style="background-color: #eee">/Signature/KeyInfo/X509Data/X509Certificate</code> obsahuje Base64
        enkódovanou DER formu X509 certifikátu (v tomto případě certifikát pro elektronickou pečeť od CA PostSignum)
    </li>
</ul>

<aside>
    <p>
        Pro účely vysvětlení budeme používat SAML, tedy nás zajímá, že v metadatech jsou poskytnuty služby "Logout"
        (režim HTTP-Redirect) a "SignOn" (HTTP-Redirect i HTTP-POST) a URL adresy, na které máme uživatele přesměrovat
        nebo kam máme odeslat data
    </p>
</aside>

<h2>Dekódovaný certifikát z metadat</h2>
<pre><code class="x509">
Certificate:
    Data:
        Version: 3 (0x2)
        Serial Number: 22598759 (0x158d467)
        Signature Algorithm: sha256WithRSAEncryption
        Issuer: C = CZ, organizationIdentifier = NTRCZ-47114983, O = "\C4\8Cesk\C3\A1 po\C5\A1ta, s.p.", CN = PostSignum Qualified CA 4
        Validity
            Not Before: Mar 11 07:51:26 2022 GMT
            Not After : Mar 31 07:51:26 2023 GMT
        Subject: C = CZ, organizationIdentifier = NTRCZ-72054506, O = Spr\C3\A1va z\C3\A1kladn\C3\ADch registr\C5\AF, CN = GG_FPSTS_TEST, serialNumber = S275730
        Subject Public Key Info:
            Public Key Algorithm: rsaEncryption
                Public-Key: (2048 bit)
                Modulus:
                    00:b9:2e:cd:e9:5a:73:68:85:4b:50:3e:fb:13:92:
                    a2:db:06:ce:34:fd:8c:74:e5:e2:c6:43:c3:df:1e:
                    db:99:ae:20:80:8c:73:aa:68:75:0d:f4:a3:88:bc:
                    f5:74:a5:d8:0b:cb:83:d8:0a:93:a3:39:fd:84:43:
                    23:ae:3f:4a:01:93:b1:ea:db:ef:5a:b4:86:5b:03:
                    4d:7f:49:4c:a7:47:6c:3a:1b:20:28:b0:27:68:48:
                    9c:16:05:1e:25:a7:ad:8b:77:4c:06:83:c8:25:80:
                    ef:37:a5:e0:03:0b:bc:88:71:9f:8c:f5:98:e1:25:
                    e5:9c:c3:23:fb:d4:07:cf:df:93:1d:20:97:4b:cd:
                    a1:97:ea:9a:d3:f6:f8:94:c2:ee:4e:f3:1a:ef:b3:
                    5a:6a:67:c9:2c:93:66:51:88:b7:3d:79:35:1a:89:
                    5a:0c:67:7e:74:db:a1:4c:4f:cd:87:15:d7:19:32:
                    e3:22:d2:61:52:24:bc:3d:f2:90:3d:d8:e0:22:fd:
                    61:a1:cb:58:a1:87:e2:c6:c4:ad:81:6c:5c:84:e8:
                    50:fc:cf:f7:0a:49:ab:be:43:4b:fe:e7:b5:a0:ea:
                    05:aa:fa:98:bc:65:f3:79:5d:10:a6:58:e0:84:ce:
                    92:17:7a:10:10:e3:1e:aa:ce:e8:66:fd:18:5a:2d:
                    13:d5
                Exponent: 65537 (0x10001)
        X509v3 extensions:
            X509v3 Certificate Policies:
                Policy: 2.23.134.1.4.1.18.200
                  User Notice:
                    Explicit Text: Tento kvalifikovany certifikat pro elektronickou pecet byl vydan v souladu s narizenim EU c. 910/2014.This is a qualified certificate for electronic seal according to Regulation (EU) No 910/2014.
                  CPS: http://www.postsignum.cz
                Policy: 0.4.0.194112.1.1
            qcStatements:
                0..0......F..0j.....F..0`0..(https://www.postsignum.cz/pds/pds_en.pdf..en0..(https://www.postsignum.cz/pds/pds_cs.pdf..cs0......F..0......F...
            Authority Information Access:
                CA Issuers - URI:http://crt.postsignum.cz/crt/psqualifiedca4.crt
                OCSP - URI:http://ocsp.postsignum.cz/OCSP/QCA4/
            X509v3 Key Usage: critical
                Digital Signature, Non Repudiation, Key Encipherment
            X509v3 Extended Key Usage:
                E-mail Protection, 1.3.6.1.4.1.311.10.3.12
            X509v3 Authority Key Identifier:
                0F:28:7C:3E:36:00:38:10:50:AE:3D:B8:21:97:8B:F7:60:5C:61:78
            X509v3 CRL Distribution Points:
                Full Name:
                  URI:http://crl.postsignum.cz/crl/psqualifiedca4.crl
                Full Name:
                  URI:http://crl2.postsignum.cz/crl/psqualifiedca4.crl
                Full Name:
                  URI:http://crl.postsignum.eu/crl/psqualifiedca4.crl
            X509v3 Subject Key Identifier:
                82:B5:2B:73:9E:0A:3C:A3:44:26:3B:94:AA:3D:2D:66:7C:F8:6B:15
    Signature Algorithm: sha256WithRSAEncryption
    Signature Value:
        33:ed:22:cd:56:8d:25:a9:13:83:c1:c7:86:41:72:60:0b:a9:
        0a:cc:eb:f0:1d:10:de:96:80:b0:d9:2b:6c:44:29:14:af:61:
        18:ec:41:ef:a9:6a:6a:69:5f:3d:28:32:76:6a:80:bc:10:97:
        6e:01:50:f4:a4:36:4d:9f:b1:f5:f3:6e:60:00:49:1c:f5:5b:
        04:09:de:80:03:e0:0e:18:ff:1e:34:82:63:5e:52:14:3d:d4:
        e0:3d:5e:42:d6:7e:94:dd:4a:28:c6:0e:20:c8:46:63:ac:22:
        32:44:17:87:4f:b8:35:0f:6d:26:80:7c:77:21:45:12:da:16:
        ff:09:be:45:c1:72:32:a6:0a:97:35:4f:1c:76:43:8a:00:da:
        95:37:47:90:5a:10:9f:22:b4:18:27:c4:de:68:52:59:12:d3:
        f1:b2:88:f6:e9:bc:c5:d8:a7:6c:ce:15:8e:63:2e:a1:c4:36:
        af:f4:e9:3b:49:2f:27:2f:db:57:43:e8:fe:8d:18:45:93:30:
        dc:4c:53:3a:a7:76:b9:b1:65:b4:31:3f:33:62:f1:23:ee:8e:
        9d:12:13:8d:5f:be:5c:05:94:72:b0:d1:63:3a:be:48:b4:ff:
        9b:12:0e:db:4e:e3:1a:a7:17:53:1c:51:8d:65:8f:3c:11:4e:
        59:2b:55:94:9e:30:59:e9:c0:7a:5d:17:5b:59:80:30:5c:26:
        3b:02:7d:b2:08:b1:d8:e7:54:62:d7:82:22:aa:5a:92:03:35:
        de:d4:6d:49:4f:93:09:5d:f7:32:b4:69:6f:10:7d:4f:9d:2e:
        70:6e:90:60:82:c3:09:e4:aa:e9:0e:39:ce:a8:bc:77:fd:b0:
        99:7e:6f:21:5d:e7:f1:34:f4:5f:23:7e:fc:aa:d7:e6:42:0e:
        e6:8c:d3:9c:f2:06:b4:88:7a:fc:2e:23:9f:c8:b4:27:86:75:
        57:27:2e:3f:45:7d:44:f2:f9:e6:16:43:24:56:2e:4c:7c:6e:
        06:9d:c2:d3:0a:64:8d:1b:3c:e8:9f:17:f5:d2:46:f8:4c:76:
        4c:27:69:df:3c:7e:57:63:d1:0d:9b:fb:4b:fe:1c:53:a6:a1:
        d1:82:54:5d:2d:8b:64:cd:57:e2:b4:f3:1c:ae:f7:eb:b2:dc:
        59:db:c8:b2:60:e9:d4:3e:c8:f4:0c:01:b0:32:cc:02:7e:12:
        74:49:a3:05:ad:91:3b:dc:31:dd:21:f1:90:6f:49:14:4c:a2:
        11:3d:fc:97:a9:23:98:5a:99:b5:10:59:39:2b:b6:03:49:41:
        d2:70:c4:c4:9e:01:10:db:86:15:0e:6a:fa:5a:27:6b:34:98:
        f9:ee:e2:eb:6d:c5:85:08
</code></pre>
