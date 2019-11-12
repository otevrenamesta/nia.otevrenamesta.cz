<?php

namespace App\Saml;

use Cake\Controller\Controller;
use Cake\Filesystem\File;
use Cake\Http\Exception\NotImplementedException;
use Cake\Log\Log;
use Cake\Utility\Text;
use Psr\Log\LoggerInterface;
use SAML2\Compat\AbstractContainer;
use SAML2\XML\saml\Issuer;

class NiaContainer extends AbstractContainer
{
    private $id = false;
    private $controller = false;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Generate a random identifier for identifying SAML2 documents.
     * @return string
     */
    public function generateId(): string
    {
        if ($this->id === false) {
            $this->id = '_' . Text::uuid();
        }
        return $this->id;
    }

    /**
     * Log an incoming message to the debug log.
     *
     * Type can be either:
     * - **in** XML received from third party
     * - **out** XML that will be sent to third party
     * - **encrypt** XML that is about to be encrypted
     * - **decrypt** XML that was just decrypted
     *
     * @param \DOMElement|string $message
     * @param string $type
     * @return void
     */
    public function debugMessage($message, string $type): void
    {
        $this->getLogger()->debug($type);
        $this->getLogger()->debug($message);
    }

    /**
     * Get a PSR-3 compatible logger.
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return Log::engine('debug');
    }

    /**
     * Trigger the user to perform a GET to the given URL with the given data.
     *
     * @param string $url
     * @param array $data
     * @return void
     */
    public function redirect(string $url, array $data = []): void
    {
        $params = http_build_query($data);
        $this->controller->redirect($url . '?' . $params);
    }

    /**
     * Trigger the user to perform a POST to the given URL with the given data.
     *
     * @param string $url
     * @param array $data
     * @return void
     */
    public function postRedirect(string $url, array $data = []): void
    {
        throw  new NotImplementedException('NiaContainer::postRedirect');
    }

    /**
     * This function retrieves the path to a directory where temporary files can be saved.
     *
     * @return string Path to a temporary directory, without a trailing directory separator.
     * @throws \Exception If the temporary directory cannot be created or it exists and does not belong
     * to the current user.
     */
    public function getTempDir(): string
    {
        return TMP;
    }

    /**
     * Atomically write a file.
     *
     * This is a helper function for writing data atomically to a file. It does this by writing the file data to a
     * temporary file, then renaming it to the required file name.
     *
     * @param string $filename The path to the file we want to write to.
     * @param string $data The data we should write to the file.
     * @param int $mode The permissions to apply to the file. Defaults to 0600.
     * @return void
     */
    public function writeFile(string $filename, string $data, int $mode = null): void
    {
        $file = new File(TMP . DS . $filename);
        $file->write($data, $mode);
        $file->close();
    }

    public function getIssuer(): Issuer
    {
        $issuer = new Issuer();
        $issuer->setValue(NiaServiceProvider::$IssuerURL);
        return $issuer;
    }
}