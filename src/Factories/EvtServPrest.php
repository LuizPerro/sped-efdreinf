<?php

namespace NFePHP\EFDReinf\Factories;

/**
 * Class EFD-Reinf EvtServPrest Event R-2020 constructor
 *
 * @category  Library
 * @package   NFePHP\EFDReinf
 * @copyright NFePHP Copyright (c) 2017 - 2021
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-efdreinf for the canonical source repository
 */

use NFePHP\EFDReinf\Common\Factory;
use NFePHP\EFDReinf\Common\FactoryInterface;
use NFePHP\EFDReinf\Common\FactoryId;
use NFePHP\Common\Certificate;
use NFePHP\Common\Strings;
use NFePHP\EFDReinf\Factories\Traits\FormatNumber;
use NFePHP\EFDReinf\Factories\Traits\RegraNomeValido;
use stdClass;

class EvtServPrest extends Factory implements FactoryInterface
{
    use FormatNumber, RegraNomeValido;

    /**
     * Constructor
     * @param string $config
     * @param stdClass $std
     * @param Certificate $certificate
     * @param string $data
     */
    public function __construct(
        $config,
        stdClass $std,
        Certificate $certificate = null,
        $data = null
    ) {
        $params = new \stdClass();
        $params->evtName = 'evtPrestadorServicos';
        $params->evtTag = 'evtServPrest';
        $params->evtAlias = 'R-2020';
        parent::__construct($config, $std, $params, $certificate, $data);
    }

    /**
     * Node constructor
     */
    protected function toNode()
    {
        $ideContri = $this->node->getElementsByTagName('ideContri')->item(0);
        //o idEvento pode variar de evento para evento
        //então cada factory individualmente terá de construir o seu
        $ideEvento = $this->dom->createElement("ideEvento");
        $this->dom->addChild(
            $ideEvento,
            "indRetif",
            $this->std->indretif,
            true
        );
        if ($this->std->indretif == 2 && empty($this->std->nrrecibo)) {
            throw new \Exception("Para retificar o evento DEVE ser informado o "
                . "número do RECIBO do evento anterior que está retificando.");
        }
        $this->dom->addChild(
            $ideEvento,
            "nrRecibo",
            !empty($this->std->nrrecibo) ? $this->std->nrrecibo : null,
            $this->std->indretif == 2 ? true : false
        );
        $this->dom->addChild(
            $ideEvento,
            "perApur",
            $this->std->perapur,
            true
        );
        $this->dom->addChild(
            $ideEvento,
            "tpAmb",
            $this->tpAmb,
            true
        );
        $this->dom->addChild(
            $ideEvento,
            "procEmi",
            $this->procEmi,
            true
        );
        $this->dom->addChild(
            $ideEvento,
            "verProc",
            $this->verProc,
            true
        );
        $this->node->insertBefore($ideEvento, $ideContri);

        $info = $this->dom->createElement("infoServPrest");
        $ideEstabPrest = $this->dom->createElement("ideEstabPrest");
        $this->dom->addChild(
            $ideEstabPrest,
            "tpInscEstabPrest",
            "1",
            true
        );
        $this->dom->addChild(
            $ideEstabPrest,
            "nrInscEstabPrest",
            $this->std->nrinscestabprest,
            true
        );
        $ideTomador = $this->dom->createElement("ideTomador");
        $this->dom->addChild(
            $ideTomador,
            "tpInscTomador",
            $this->std->tpinsctomador,
            true
        );
        $this->dom->addChild(
            $ideTomador,
            "nrInscTomador",
            $this->std->nrinsctomador,
            true
        );
        $this->dom->addChild(
            $ideTomador,
            "indObra",
            $this->std->indobra,
            true
        );
        $this->dom->addChild(
            $ideTomador,
            "vlrTotalBruto",
            self::format($this->std->vlrtotalbruto, 2, $this->decimalSeparator),
            true
        );
        $this->dom->addChild(
            $ideTomador,
            "vlrTotalBaseRet",
            self::format($this->std->vlrtotalbaseret, 2, $this->decimalSeparator),
            true
        );
        $this->dom->addChild(
            $ideTomador,
            "vlrTotalRetPrinc",
            self::format($this->std->vlrtotalretprinc, 2, $this->decimalSeparator),
            true
        );
        $this->dom->addChild(
            $ideTomador,
            "vlrTotalRetAdic",
            self::format($this->std->vlrtotalretadic ?? null, 2, $this->decimalSeparator),
            false
        );
        $this->dom->addChild(
            $ideTomador,
            "vlrTotalNRetPrinc",
            self::format($this->std->vlrtotalnretprinc ?? null, 2, $this->decimalSeparator),
            false
        );
        $this->dom->addChild(
            $ideTomador,
            "vlrTotalNRetAdic",
            self::format($this->std->vlrtotalnretadic ?? null, 2, $this->decimalSeparator),
            false
        );

        foreach ($this->std->nfs as $n) {
            $nfs = $this->dom->createElement("nfs");
            $this->dom->addChild(
                $nfs,
                "serie",
                $n->serie,
                true
            );
            $this->dom->addChild(
                $nfs,
                "numDocto",
                $n->numdocto,
                true
            );
            $this->dom->addChild(
                $nfs,
                "dtEmissaoNF",
                $n->dtemissaonf,
                true
            );
            $this->dom->addChild(
                $nfs,
                "vlrBruto",
                self::format($n->vlrbruto, 2, $this->decimalSeparator),
                true
            );
            $this->dom->addChild(
                $nfs,
                "obs",
                !empty($n->obs) ? Strings::replaceUnacceptableCharacters($n->obs) : null,
                false
            );

            foreach ($n->infotpserv as $its) {
                $infoTpServ = $this->dom->createElement("infoTpServ");
                $this->dom->addChild(
                    $infoTpServ,
                    "tpServico",
                    $its->tpservico,
                    true
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrBaseRet",
                    self::format($its->vlrbaseret, 2, $this->decimalSeparator),
                    true
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrRetencao",
                    self::format($its->vlrretencao, 2, $this->decimalSeparator),
                    true
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrRetSub",
                    self::format($its->vlrretsub ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrNRetPrinc",
                    self::format($its->vlrnretprinc ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrServicos15",
                    self::format($its->vlrservicos15 ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrServicos20",
                    self::format($its->vlrservicos20 ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrServicos25",
                    !self::format($its->vlrservicos25 ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrAdicional",
                    self::format($its->vlradicional ?? null, 2, $this->decimalSeparator),
                    false
                );
                $this->dom->addChild(
                    $infoTpServ,
                    "vlrNRetAdic",
                    self::format($its->vlrnretadic ?? null, 2, $this->decimalSeparator),
                    false
                );

                $nfs->appendChild($infoTpServ);
            }
            $ideTomador->appendChild($nfs);
        }
        if (!empty($this->std->infoprocretpr)) {
            foreach ($this->std->infoprocretpr as $irp) {
                $infoProcRetPr = $this->dom->createElement("infoProcRetPr");
                $this->dom->addChild(
                    $infoProcRetPr,
                    "tpProcRetPrinc",
                    $irp->tpprocretprinc,
                    true
                );
                $this->dom->addChild(
                    $infoProcRetPr,
                    "nrProcRetPrinc",
                    $irp->nrprocretprinc,
                    true
                );
                $this->dom->addChild(
                    $infoProcRetPr,
                    "codSuspPrinc",
                    !empty($irp->codsuspprinc) ? $irp->codsuspprinc : null,
                    false
                );
                $this->dom->addChild(
                    $infoProcRetPr,
                    "valorPrinc",
                    self::format($irp->valorprinc, 2, $this->decimalSeparator),
                    true
                );
                $ideTomador->appendChild($infoProcRetPr);
            }
        }
        if (!empty($this->std->infoprocretad)) {
            foreach ($this->std->infoprocretad as $rad) {
                $infoProcRetAd = $this->dom->createElement("infoProcRetAd");
                $this->dom->addChild(
                    $infoProcRetAd,
                    "tpProcRetAdic",
                    $rad->tpprocretadic,
                    true
                );
                $this->dom->addChild(
                    $infoProcRetAd,
                    "nrProcRetAdic",
                    $rad->nrprocretadic,
                    true
                );
                $this->dom->addChild(
                    $infoProcRetAd,
                    "codSuspAdic",
                    !empty($rad->codsuspadic) ? $rad->codsuspadic : null,
                    false
                );
                $this->dom->addChild(
                    $infoProcRetAd,
                    "valorAdic",
                    self::format($rad->valoradic, 2, $this->decimalSeparator),
                    true
                );
                $ideTomador->appendChild($infoProcRetAd);
            }
        }
        $ideEstabPrest->appendChild($ideTomador);
        $info->appendChild($ideEstabPrest);
        $this->node->appendChild($info);
        $this->reinf->appendChild($this->node);
        //$this->xml = $this->dom->saveXML($this->reinf);
        $this->sign($this->evtTag);
    }
}
