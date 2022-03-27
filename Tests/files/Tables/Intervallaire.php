<?php

namespace Core\Tests\files\Tables;

use Core\App\Mvc\Table;

class Intervallaire extends Table
{
    use \Core\Traits\Intervallaire;

    public function init()
    {
        $this->table = 'testintervallaire';
        $this->fieldPrefixe = 'TES_INT_';
        $this->fieldForce   = 'Camel';

        $this->intervallaireFieldBorneMin = 'TES_INT_BORNE_MIN';
        $this->intervallaireFieldBorneMax = 'TES_INT_BORNE_MAX';
        $this->intervallaireFieldNiveau = 'TES_INT_NIVEAU';

        $this->addField('TES_INT_ITEM'      , ['PK' => 'AI', 'entityField' => 'id']);
        $this->addField('TES_INT_COMPTE' , []);
        $this->addField('TES_INT_BORNE_MIN' , []);
        $this->addField('TES_INT_BORNE_MAX' , []);
        $this->addField('TES_INT_NIVEAU'    , []);
        $this->addField('TES_INT_LIBELLE'   , []);
    }
}