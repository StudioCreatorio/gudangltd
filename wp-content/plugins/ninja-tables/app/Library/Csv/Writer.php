<?php
/**
* This file is part of the League.csv library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/csv/
* @version 8.2.0
* @package League.csv
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace NinjaTables\App\Library\Csv;

use InvalidArgumentException;
use NinjaTables\App\Library\Csv\Modifier\RowFilter;
use NinjaTables\App\Library\Csv\Modifier\StreamIterator;
use ReflectionMethod;
use SplFileObject;
use Traversable;

/**
 *  A class to manage data insertion into a CSV
 *
 * @package League.csv
 * @since  4.0.0
 *
 */
class Writer extends AbstractCsv
{
    use RowFilter;

    /**
     * @inheritdoc
     */
    protected $stream_filter_mode = STREAM_FILTER_WRITE;

    /**
     * The CSV object holder
     *
     * @var SplFileObject|StreamIterator
     */
    protected $csv;

    /**
     * fputcsv method from SplFileObject or StreamIterator
     *
     * @var ReflectionMethod
     */
    protected $fputcsv;

    /**
     * Nb parameters for SplFileObject::fputcsv method
     *
     * @var integer
     */
    protected $fputcsv_param_count;

    /**
     * Adds multiple lines to the CSV document
     *
     * a simple wrapper method around insertOne
     *
     * @param Traversable|array $rows a multidimensional array or a Traversable object
     *
     * @throws InvalidArgumentException If the given rows format is invalid
     *
     * @return static
     */
    public function insertAll($rows)
    {
        if (!is_array($rows) && !$rows instanceof Traversable) {
            throw new InvalidArgumentException(
                'the provided data must be an array OR a `Traversable` object'
            );
        }

        foreach ($rows as $row) {
            $this->insertOne($row);
        }

        return $this;
    }

    /**
     * Adds a single line to a CSV document
     *
     * @param string[]|string $row a string, an array or an object implementing to '__toString' method
     *
     * @return static
     */
    public function insertOne($row)
    {
        if (!is_array($row)) {
            $row = str_getcsv($row, $this->delimiter, $this->enclosure, $this->escape);
        }
        $row = $this->formatRow($row);
        $this->validateRow($row);
        $this->addRow($row);

        return $this;
    }

    /**
     * Add new record to the CSV document
     *
     * @param array $row record to add
     */
    protected function addRow(array $row)
    {
        $this->initCsv();
        $this->fputcsv->invokeArgs($this->csv, $this->getFputcsvParameters($row));
        if ("\n" !== $this->newline) {
            $this->csv->fseek(-1, SEEK_CUR);
            $this->csv->fwrite($this->newline, strlen($this->newline));
        }
    }

    /**
     * Initialize the CSV object and settings
     */
    protected function initCsv()
    {
        if (null !== $this->csv) {
            return;
        }

        $this->csv = $this->getIterator();
        $this->fputcsv = new ReflectionMethod(get_class($this->csv), 'fputcsv');
        $this->fputcsv_param_count = $this->fputcsv->getNumberOfParameters();
    }

    /**
     * returns the parameters for SplFileObject::fputcsv
     *
     * @param array $fields The fields to be add
     *
     * @return array
     */
    protected function getFputcsvParameters(array $fields)
    {
        $parameters = [$fields, $this->delimiter, $this->enclosure];
        if (4 == $this->fputcsv_param_count) {
            $parameters[] = $this->escape;
        }

        return $parameters;
    }

    /**
     *  {@inheritdoc}
     */
    public function isActiveStreamFilter()
    {
        return parent::isActiveStreamFilter() && null === $this->csv;
    }

    /**
     *  {@inheritdoc}
     */
    public function __destruct()
    {
        $this->csv = null;
        parent::__destruct();
    }
}
