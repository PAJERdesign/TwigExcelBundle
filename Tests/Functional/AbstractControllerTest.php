<?php

namespace MewesK\TwigExcelBundle\Tests\Functional;

use InvalidArgumentException;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Reader_Excel5;
use PHPExcel_Reader_OOCalc;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AbstractControllerTest
 * @package MewesK\TwigExcelBundle\Tests\Functional
 */
abstract class AbstractControllerTest extends WebTestCase
{
    protected static $TEMP_PATH = '/../../tmp/functional/';

    /**
     * @var Client
     */
    protected static $client;
    /**
     * @var Router
     */
    protected static $router;

    //
    // Helper
    //

    /**
     * @param $uri
     * @param $format
     * @return \PHPExcel
     */
    protected function getDocument($uri, $format = 'xlsx')
    {
        // generate source
        static::$client->request('GET', $uri);
        $source = static::$client->getResponse()->getContent();

        // create source directory if necessary
        if (!file_exists(__DIR__ . static::$TEMP_PATH)) {
            mkdir(__DIR__ . static::$TEMP_PATH);
        }

        // save source
        file_put_contents(__DIR__ . static::$TEMP_PATH . 'simple' . '.' . $format, $source);

        // load source
        switch ($format) {
            case 'ods':
                $reader = new PHPExcel_Reader_OOCalc();
                break;
            case 'xls':
                $reader = new PHPExcel_Reader_Excel5();
                break;
            case 'xlsx':
                $reader = new PHPExcel_Reader_Excel2007();
                break;
            default:
                throw new InvalidArgumentException();
        }

        return $reader->load(__DIR__ . static::$TEMP_PATH . 'simple' . '.' . $format);
    }

    //
    // PhpUnit
    //

    /**
     * @return array
     */
    public abstract function formatProvider();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__ . static::$TEMP_PATH);

        static::$client = static::createClient();
        static::$router = static::$kernel->getContainer()->get('router');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if (in_array(getenv('DELETE_TEMP_FILES'), ['true', '1', 1, true], true)) {
            $fs = new Filesystem();
            $fs->remove(__DIR__ . static::$TEMP_PATH);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = array())
    {
        return static::$kernel = new AppKernel(
            array_key_exists('config', $options) ? $options['config'] : 'config.yml'
        );
    }
}