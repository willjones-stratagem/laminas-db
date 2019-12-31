<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\TableGateway\Feature;

use Laminas\Db\Metadata\Metadata;
use Laminas\Db\Metadata\MetadataInterface;
use Laminas\Db\TableGateway\Exception;

/**
 * @category   Laminas
 * @package    Laminas_Db
 * @subpackage TableGateway
 */
class MetadataFeature extends AbstractFeature
{

    /**
     * @var MetadataInterface
     */
    protected $metadata = null;

    /**
     * Constructor
     *
     * @param MetadataInterface $metadata
     */
    public function __construct(MetadataInterface $metadata = null)
    {
        if ($metadata) {
            $this->metadata = $metadata;
        }
        $this->sharedData['metadata'] = array(
            'primaryKey' => null,
            'columns' => array()
        );
    }

    public function postInitialize()
    {
        if ($this->metadata == null) {
            $this->metadata = new Metadata($this->tableGateway->adapter);
        }

        // localize variable for brevity
        $t = $this->tableGateway;
        $m = $this->metadata;

        // get column named
        $columns = $m->getColumnNames($t->table);
        $t->columns = $columns;

        // set locally
        $this->sharedData['metadata']['columns'] = $columns;

        // process primary key
        $pkc = null;

        foreach ($m->getConstraints($t->table) as $constraint) {
            /** @var $constraint \Laminas\Db\Metadata\Object\ConstraintObject */
            if ($constraint->getType() == 'PRIMARY KEY') {
                $pkc = $constraint;
                break;
            }
        }

        if ($pkc === null) {
            throw new Exception\RuntimeException('A primary key for this column could not be found in the metadata.');
        }

        if (count($pkc->getColumns()) == 1) {
            $pkck = $pkc->getColumns();
            $primaryKey = $pkck[0];
        } else {
            $primaryKey = $pkc->getColumns();
        }

        $this->sharedData['metadata']['primaryKey'] = $primaryKey;
    }


}
