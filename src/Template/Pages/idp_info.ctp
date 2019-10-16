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
<p>Dle <a href="https://info.eidentita.cz/download/SeP_PriruckaKvalifikovanehoPoskytovatele.pdf">příručky
        SeP</a> jsou metadata na těchto adresách

<ol>
    <li>Testovací prostředí: <a target="_blank"
                                href="https://tnia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml">https://tnia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml</a>
    </li>
    <li>Produkční prostředí: <a target="_blank"
                                href="https://nia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml">https://nia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml</a>
    </li>
</ol>

<aside>
    <p>
        Je potřeba dodat že poskytnutí jen XML rozhraní, je sadistické rozhodnutí státní správy, se
        kterým se prostě musíme smířit
    </p>
</aside>

<h2>Ukázka XML metadat (testovací prostředí)</h2>
<script>hljs.initHighlightingOnLoad();</script>
<pre><code class="xml">&lt;EntityDescriptor xmlns=&quot;urn:oasis:names:tc:SAML:2.0:metadata&quot; ID=&quot;_8f5bbcc0-6a28-4cfe-a335-b6abf1aa089d&quot; entityID=&quot;urn:microsoft:cgg2010:fpsts&quot;&gt;

 &lt;!-- Sekce s podpisem a kontrolním součtem obsahu dokumentu (rsa-sha256) dle specifikace xmldsig --&gt;

 &lt;Signature xmlns=&quot;http://www.w3.org/2000/09/xmldsig#&quot;&gt;
  &lt;SignedInfo&gt;
   &lt;CanonicalizationMethod Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
   &lt;SignatureMethod Algorithm=&quot;http://www.w3.org/2001/04/xmldsig-more#rsa-sha256&quot;/&gt;
   &lt;Reference URI=&quot;#_8f5bbcc0-6a28-4cfe-a335-b6abf1aa089d&quot;&gt;
    &lt;Transforms&gt;
      &lt;Transform Algorithm=&quot;http://www.w3.org/2000/09/xmldsig#enveloped-signature&quot;/&gt;
      &lt;Transform Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
     &lt;/Transforms&gt;
     &lt;DigestMethod Algorithm=&quot;http://www.w3.org/2001/04/xmlenc#sha256&quot;/&gt;
     &lt;DigestValue&gt;HyRSq5AEW5BGZn1xXqhwbwHUqvicgd9wbiNls7G5f98=&lt;/DigestValue&gt;
    &lt;/Reference&gt;
   &lt;/SignedInfo&gt;
   &lt;SignatureValue&gt;
    BFNADcaYyIKI4h2l4PSukG0F+juyp/W2lBTPZ9BL3gUg1ocZKB8rzF3Pe4utoavFtKhIpEjgHvTnUGwP8+I1QkPgPOk6XnjDkz+6z6Ick/ouT4g6EQmjMU8VK0KTNme+hkMmimI0sKzpHP5w3vWZ3CGVTY0Vj/XFyg8ag9n8XfN9mbVfUHJxZDxjxEOfhEe/F3huCPoEijQDqoJqkUAjq//y2tcL6MeQ1UmD0JBmPwkdjtEMdV+RM5GP8cMNq93McZ0Rk2znk/f/cpftvnHsclWEiBRvpwLxdGh3hk/3RMjk0IEGFSpNuIZ8Q9CLWNwlb0RVGsDAd2/X/CL3K2bA0w==
   &lt;/SignatureValue&gt;
   &lt;KeyInfo&gt;
    &lt;X509Data&gt;
     &lt;X509Certificate&gt;
      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
    &lt;/X509Certificate&gt;
   &lt;/X509Data&gt;
  &lt;/KeyInfo&gt;
 &lt;/Signature&gt;

 &lt;!-- Metadata pro kofiguraci protistrany komunikující protokolem WS-Federation --&gt;

 &lt;RoleDescriptor xmlns:xsi=&quot;http://www.w3.org/2001/XMLSchema-instance&quot; xmlns:fed=&quot;http://docs.oasis-open.org/wsfed/federation/200706&quot; xsi:type=&quot;fed:SecurityTokenServiceType&quot; protocolSupportEnumeration=&quot;http://docs.oasis-open.org/wsfed/federation/200706&quot;&gt;
  &lt;KeyDescriptor use=&quot;signing&quot;&gt;
   &lt;KeyInfo xmlns=&quot;http://www.w3.org/2000/09/xmldsig#&quot;&gt;
    &lt;X509Data&gt;
     &lt;X509Certificate&gt;
      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
     &lt;/X509Certificate&gt;
    &lt;/X509Data&gt;
   &lt;/KeyInfo&gt;
  &lt;/KeyDescriptor&gt;
  &lt;fed:TokenTypesOffered&gt;
   &lt;fed:TokenType Uri=&quot;http://schemas.microsoft.com/ws/2006/05/identitymodel/tokens/Saml&quot;/&gt;
  &lt;/fed:TokenTypesOffered&gt;
  &lt;fed:ClaimTypesOffered&gt;
   &lt;auth:ClaimType xmlns:auth=&quot;http://docs.oasis-open.org/wsfed/authorization/200706&quot; Uri=&quot;http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress&quot;&gt;
    &lt;auth:DisplayName&gt;Email address&lt;/auth:DisplayName&gt;
    &lt;auth:Description&gt;The email of the subject.&lt;/auth:Description&gt;
   &lt;/auth:ClaimType&gt;
  &lt;/fed:ClaimTypesOffered&gt;
  &lt;fed:SecurityTokenServiceEndpoint&gt;
   &lt;wsa:EndpointReference xmlns:wsa=&quot;http://www.w3.org/2005/08/addressing&quot;&gt;
    &lt;wsa:Address&gt;https://tnia.eidentita.cz/FPSTS/issue.svc&lt;/wsa:Address&gt;
   &lt;/wsa:EndpointReference&gt;
  &lt;/fed:SecurityTokenServiceEndpoint&gt;
  &lt;fed:PassiveRequestorEndpoint&gt;
   &lt;wsa:EndpointReference xmlns:wsa=&quot;http://www.w3.org/2005/08/addressing&quot;&gt;
    &lt;wsa:Address&gt;https://tnia.eidentita.cz/FPSTS/default.aspx&lt;/wsa:Address&gt;
   &lt;/wsa:EndpointReference&gt;
  &lt;/fed:PassiveRequestorEndpoint&gt;
 &lt;/RoleDescriptor&gt;

 &lt;!-- Metadata pro konfiguraci protistrany komunikující protokolem SAML2.0 dle eIDAS --&gt;

 &lt;IDPSSODescriptor protocolSupportEnumeration=&quot;urn:oasis:names:tc:SAML:2.0:protocol&quot;&gt;
  &lt;KeyDescriptor use=&quot;signing&quot;&gt;
   &lt;KeyInfo xmlns=&quot;http://www.w3.org/2000/09/xmldsig#&quot;&gt;
    &lt;X509Data&gt;
     &lt;X509Certificate&gt;
      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
     &lt;/X509Certificate&gt;
    &lt;/X509Data&gt;
   &lt;/KeyInfo&gt;
  &lt;/KeyDescriptor&gt;
  &lt;SingleLogoutService Binding=&quot;urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect&quot; Location=&quot;https://tnia.eidentita.cz/FPSTS/saml2/basic&quot;/&gt;
  &lt;SingleSignOnService Binding=&quot;urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect&quot; Location=&quot;https://tnia.eidentita.cz/FPSTS/saml2/basic&quot;/&gt;
  &lt;SingleSignOnService Binding=&quot;urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST&quot; Location=&quot;https://tnia.eidentita.cz/FPSTS/saml2/basic&quot;/&gt;
 &lt;/IDPSSODescriptor&gt;
&lt;/EntityDescriptor&gt;
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
        Serial Number: 5032784 (0x4ccb50)
        Signature Algorithm: sha256WithRSAEncryption
        Issuer: C = CZ, O = "\C4\8Cesk\C3\A1 po\C5\A1ta, s.p. [I\C4\8C 47114983]", CN = PostSignum Qualified CA 3
        Validity
            Not Before: Feb  7 08:14:00 2019 GMT
            Not After : Feb 27 08:14:00 2020 GMT
        Subject: C = CZ, organizationIdentifier = NTRCZ-72054506, O = Spr\C3\A1va z\C3\A1kladn\C3\ADch registr\C5\AF [I\C4\8C 72054506], CN = GG_FPSTS_TEST, serialNumber = S275730
        Subject Public Key Info:
            Public Key Algorithm: rsaEncryption
                RSA Public-Key: (2048 bit)
                Modulus:
                    00:e3:14:82:be:71:c5:32:14:5b:02:11:51:a4:d2:
                    04:e8:66:75:51:ac:9e:8a:f7:d0:a1:3f:bd:7d:88:
                    68:ec:dc:cd:0a:50:52:26:0d:51:8b:0e:c2:df:05:
                    e0:51:c5:5d:35:7f:71:c0:35:dc:17:76:df:9a:80:
                    b4:b3:17:4e:ac:e5:0f:e0:ee:3d:c2:69:c7:d7:ae:
                    8b:7b:68:43:58:87:8e:3b:6e:20:19:ec:cb:c7:0e:
                    e0:09:73:8f:59:b3:56:ce:4d:36:d5:8c:b3:bb:cf:
                    46:08:7f:b1:36:38:bd:e0:c0:3b:73:29:5e:f6:97:
                    83:c0:0f:00:fc:2f:f5:be:33:65:e7:2c:46:ee:a8:
                    b2:ef:ca:a6:c2:3a:35:7d:32:2e:c4:16:6d:95:c0:
                    29:af:05:80:49:31:ee:a5:9c:ca:d8:5f:3c:63:60:
                    19:42:5a:da:c6:81:b9:c8:53:8e:61:11:49:5e:f1:
                    2e:8c:62:86:c8:e0:05:c3:23:59:7f:12:38:00:10:
                    10:e8:e0:ce:66:34:01:75:15:6d:81:35:ef:88:63:
                    33:8a:16:a3:d8:83:be:9a:aa:02:9c:79:5b:8a:11:
                    2a:7a:ed:d9:5a:3f:9c:a2:32:4b:0f:cc:0e:93:ce:
                    c2:90:40:5e:49:9e:14:40:38:cc:46:89:54:e6:e3:
                    08:e7
                Exponent: 65537 (0x10001)
        X509v3 extensions:
            X509v3 Subject Alternative Name:
                othername:<unsupported>
            X509v3 Certificate Policies:
                Policy: 2.23.134.1.4.1.18.120
                  User Notice:
                    Explicit Text: Tento kvalifikovany certifikat pro elektronickou pecet byl vydan v souladu s narizenim EU c. 910/2014.This is a qualified certificate for electronic seal according to Regulation (EU) No 910/2014.
                  CPS: http://www.postsignum.cz
                Policy: 0.4.0.194112.1.1

            qcStatements:
                0..0......F..0j.....F..0`0..(https://www.postsignum.cz/pds/pds_en.pdf..en0..(https://www.postsignum.cz/pds/pds_cs.pdf..cs0......F..0......F...
            Authority Information Access:
                CA Issuers - URI:http://www.postsignum.cz/crt/psqualifiedca3.crt
                CA Issuers - URI:http://www2.postsignum.cz/crt/psqualifiedca3.crt
                CA Issuers - URI:http://postsignum.ttc.cz/crt/psqualifiedca3.crt
                OCSP - URI:http://ocsp.postsignum.cz/OCSP/QCA3/

            X509v3 Key Usage: critical
                Digital Signature, Non Repudiation, Key Encipherment
            X509v3 Authority Key Identifier:
                keyid:F2:F8:CC:2A:57:61:DA:2B:17:33:59:E5:82:2D:EC:06:1C:8A:4F:4A

            X509v3 CRL Distribution Points:

                Full Name:
                  URI:http://www.postsignum.cz/crl/psqualifiedca3.crl

                Full Name:
                  URI:http://www2.postsignum.cz/crl/psqualifiedca3.crl

                Full Name:
                  URI:http://postsignum.ttc.cz/crl/psqualifiedca3.crl

            X509v3 Subject Key Identifier:
                12:8F:0E:4C:85:CD:01:29:55:9A:FB:40:2D:7D:95:A8:63:61:C4:54
    Signature Algorithm: sha256WithRSAEncryption
         3c:55:c5:03:b5:12:f0:3d:93:1d:0e:e0:18:a9:93:0f:28:9c:
         28:f9:6c:02:6f:d2:17:c0:9f:30:60:13:d3:d9:63:73:a1:c4:
         06:1a:76:25:c2:ce:39:cc:b1:f3:52:bc:04:1a:58:4d:12:91:
         66:39:1a:af:69:32:fd:13:6e:7a:68:da:8e:92:da:3b:2b:8e:
         0c:10:f6:6b:89:5f:6f:5f:0e:26:b4:8d:00:17:57:a6:16:b8:
         49:70:b6:55:a7:b4:b9:b9:65:62:6e:8f:92:89:7b:9f:a8:60:
         b8:bd:a1:78:23:f5:99:69:7c:1d:ed:e2:ad:ec:2f:db:4f:47:
         03:37:91:e6:53:5a:15:68:93:69:0d:86:e8:c7:bc:0b:34:b9:
         8b:db:4e:4a:50:70:af:08:4e:60:32:1c:84:3f:2a:8f:46:29:
         d8:a3:04:20:60:e3:fc:3f:77:6f:2d:5e:66:6c:48:af:ea:06:
         fb:92:60:b2:7f:11:32:15:da:c6:05:ac:8a:2a:aa:0c:41:12:
         81:2c:ae:61:fa:53:49:79:92:7a:42:2c:a2:56:14:86:e7:19:
         33:e7:a4:ad:c0:5b:33:f8:bb:93:bf:f6:68:55:f6:ef:61:45:
         fd:14:f0:f4:56:13:e8:9a:3f:f0:7a:85:2d:8a:9a:8c:29:f8:
         1b:79:e3:d4
</code></pre>
