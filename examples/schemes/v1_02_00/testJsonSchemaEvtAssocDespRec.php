<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../../../bootstrap.php';

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

$evento = 'evtAssocDespRec';
$version = '1_02_00';

$jsonSchema = '{
    "title": "evtAssocDespRec",
    "type": "object",
    "properties": {
        "sequencial": {
            "required": true,
            "type": "integer",
            "minimum": 1,
            "maximum": 99999
        },
        "indretif": {
            "required": true,
            "type": "integer",
            "minimum": 1,
            "maximum": 2
        },
        "nrrecibo": {
            "required": false,
            "type": ["string","null"],
            "maxLength": 52
        },
        "perapur": {
            "required": true,
            "type": "string",
            "pattern": "^(19[0-9][0-9]|2[0-9][0-9][0-9])[-/](0?[1-9]|1[0-2])$"
        },
        "tpinscestabprest": {
            "required": true,
            "type": "integer",
            "minimum": 1,
            "maximum": 1
        },
        "nrinscestabprest": {
            "required": true,
            "type": "string",
            "maxLength": 14,
            "pattern": "^[0-9]"
        },
        "recursosrec": {
            "required": true,
            "type": "array",
            "minItems": 1,
            "maxItems": 500,
            "items": {
                "type": "object",
                "properties": {
                    "cnpjorigrecurso": {
                        "required": true,
                        "type": "string",
                        "maxLength": 14,
                        "pattern": "^[0-9]"
                    },    
                    "vlrtotalrec": {
                        "required": true,
                        "type": "number"
                    },
                    "vlrtotalret": {
                        "required": true,
                        "type": "number"
                    },
                    "vlrtotalnret": {
                        "required": false,
                        "type": ["number","null"]
                    },
                    "inforecurso": {
                        "required": true,
                        "type": "array",
                        "minItems": 1,
                        "maxItems": 500,
                        "items": {
                            "type": "object",
                            "properties": {
                                "tprepasse": {
                                    "required": true,
                                    "type": "integer",
                                    "minimum": 1,
                                    "maximum": 5
                                },
                                "descrecurso": {
                                    "required": true,
                                    "type": "string",
                                    "maxLength": 20
                                },
                                "vlrbruto": {
                                    "required": true,
                                    "type": "number"
                                },
                                "vlrretapur": {
                                    "required": true,
                                    "type": "number"
                                }
                            }
                        }    
                    },
                    "infoproc": {
                        "required": false,
                        "type": ["array","null"],
                        "minItems": 0,
                        "maxItems": 50,
                        "items": {
                            "type": "object",
                            "properties": {
                                "tpproc": {
                                    "required": true,
                                    "type": "integer",
                                    "minimum": 1,
                                    "maximum": 2
                                },
                                "nrproc": {
                                    "required": true,
                                    "type": "string",
                                    "maxLength": 21
                                },
                                "codsusp": {
                                    "required": false,
                                    "type": ["string","null"],
                                    "maxLength": 14,
                                    "pattern": "^[0-9]"
                                },
                                "vlrnret": {
                                    "required": true,
                                    "type": "number"
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}';


$std = new \stdClass();
$std->sequencial = 1;
$std->indretif = 1;
$std->nrrecibo = '737373737373737';
$std->perapur = '2017-11';
$std->tpinscestabprest = 1;
$std->nrinscestabprest = '12345678901234';

$std->recursosrec[0] = new \stdClass();
$std->recursosrec[0]->cnpjorigrecurso = '12345678901234';
$std->recursosrec[0]->vlrtotalrec = 1000;
$std->recursosrec[0]->vlrtotalret = 100;
$std->recursosrec[0]->vlrtotalnret = 10;

$std->recursosrec[0]->inforecurso[0] = new \stdClass();
$std->recursosrec[0]->inforecurso[0]->tprepasse = 3;
$std->recursosrec[0]->inforecurso[0]->descrecurso = 'sei la';
$std->recursosrec[0]->inforecurso[0]->vlrbruto = 5000;
$std->recursosrec[0]->inforecurso[0]->vlrretapur = 500;

$std->recursosrec[0]->infoproc[0] = new \stdClass();
$std->recursosrec[0]->infoproc[0]->tpproc = 1;
$std->recursosrec[0]->infoproc[0]->nrproc = 'ABC21';
$std->recursosrec[0]->infoproc[0]->codsusp = '12345678901234';
$std->recursosrec[0]->infoproc[0]->vlrnret = 1000.66;


// Schema must be decoded before it can be used for validation
$jsonSchemaObject = json_decode($jsonSchema);
if (empty($jsonSchemaObject)) {
    echo "<h2>Erro de digitação no schema ! Revise</h2>";
    echo "<pre>";
    print_r($jsonSchema);
    echo "</pre>";
    die();
}
// The SchemaStorage can resolve references, loading additional schemas from file as needed, etc.
$schemaStorage = new SchemaStorage();

// This does two things:
// 1) Mutates $jsonSchemaObject to normalize the references (to file://mySchema#/definitions/integerData, etc)
// 2) Tells $schemaStorage that references to file://mySchema... should be resolved by looking in $jsonSchemaObject
$schemaStorage->addSchema('file://mySchema', $jsonSchemaObject);

// Provide $schemaStorage to the Validator so that references can be resolved during validation
$jsonValidator = new Validator(new Factory($schemaStorage));

// Do validation (use isValid() and getErrors() to check the result)
$jsonValidator->validate(
    $std,
    $jsonSchemaObject,
    Constraint::CHECK_MODE_COERCE_TYPES  //tenta converter o dado no tipo indicado no schema
);

if ($jsonValidator->isValid()) {
    echo "The supplied JSON validates against the schema.<br/>";
} else {
    echo "JSON does not validate. Violations:<br/>";
    foreach ($jsonValidator->getErrors() as $error) {
        echo sprintf("[%s] %s<br/>", $error['property'], $error['message']);
    }
    die;
}
//salva se sucesso
file_put_contents("../../../jsonSchemes/v$version/$evento.schema", $jsonSchema);
