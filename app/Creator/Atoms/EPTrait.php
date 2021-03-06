<?php
declare(strict_types=1);

namespace App\Creator\Atoms;

/**
 * @author reinhardt
 */
class EPTrait extends EPAtom
{
    CONST PSY_CHI_TRAIT_NAME   = "Psi I";
    CONST PSY_GAMMA_TRAIT_NAME = "Psi II";

    static $POSITIVE_TRAIT = 'POS';
    static $NEGATIVE_TRAIT = 'NEG';
    
    static $EGO_TRAIT = 'EGO';
    static $MORPH_TRAIT = 'MOR';
    
    //GUI use for filtering the lists
    static $CAN_USE_EVERYBODY = 'EVERY';
    static $CAN_USE_BIO = 'BIO';
    static $CAN_USE_SYNTH = 'SYNTH';
    static $CAN_USE_POD = 'POD';
    /*
     * An enum value of [$CAN_USE_EVERYBODY, $CAN_USE_BIO, $CAN_USE_SYNTH, $CAN_USE_POD]
     * @var string
     */
    public $canUse;

    /*
     * TODO: Convert this to getters for positive, negative, and neutral (which is distinguished by costing 0CP)
     * @var string
     */
    public $traitPosNeg;
    /*
     * TODO:  Convert this to a private bool with a getter
     * @var string
     */
    public $traitEgoMorph;
    /*
     * TODO: Convert this to a private variable with a getter
     * @var int
     */
    public $cpCost;

    /**
     * TODO: Convert this to a private variable with a getter
     * @var int
     */
    public $level;

    /**
     * @var EPBonusMalus[]
     */
    public $bonusMalus;
    
    
    function getSavePack(): array
    {
        $savePack = parent::getSavePack();
        
        $savePack['canUse'] = $this->canUse;

        $savePack['traitPosNeg'] =  $this->traitPosNeg;
        $savePack['traitEgoMorph'] =  $this->traitEgoMorph;
        $savePack['cpCost'] =  $this->cpCost;

        $savePack['level'] =  $this->level;
        
        $bmSavePacks = array();
        foreach($this->bonusMalus as $m){
            array_push($bmSavePacks	, $m->getSavePack());
        }
        $savePack['bmSavePacks'] = $bmSavePacks;

        //Included for backwards compatibility
        $savePack['mandatory'] = null;

        return $savePack;
    }

    /**
     * @param array $an_array
     * @return EPTrait
     */
    public static function __set_state(array $an_array)
    {
        $object = new self((string)$an_array['name'], '', '', 0);
        parent::set_state_helper($object, $an_array);

        $object->canUse        = (string)$an_array['canUse'];
        $object->traitPosNeg   = (string)$an_array['traitPosNeg'];
        $object->traitEgoMorph = (string)$an_array['traitEgoMorph'];
        $object->cpCost        = (int)$an_array['cpCost'];
        $object->level         = (int)$an_array['level'];
        foreach ($an_array['bmSavePacks'] as $m) {
            array_push($object->bonusMalus, EPBonusMalus::__set_state($m));
        }

        return $object;
    }

    /**
     * EPTrait constructor.
     * @param string         $name
     * @param string         $description
     * @param string         $traitPosNeg     Traits can be positive, negative, or neutral.  However, neutral traits are distinguished by costing 0CP
     * @param string         $traitEgoMorph
     * @param int            $cpCost
     * @param EPBonusMalus[] $bonusMalusArray
     * @param int            $level
     * @param string         $canUse          An enum value of [$CAN_USE_EVERYBODY, $CAN_USE_BIO, $CAN_USE_SYNTH, $CAN_USE_POD]
     */
    function __construct(
        string $name,
        string $traitPosNeg,
        string $traitEgoMorph,
        int $cpCost,
        string $description = '',
        array $bonusMalusArray = array(),
        int $level = 1,
        string $canUse = 'EVERY'
    ) {
        parent::__construct($name, $description);
        $this->traitPosNeg = $traitPosNeg;
        $this->traitEgoMorph = $traitEgoMorph;
        $this->cpCost = $cpCost;
        $this->bonusMalus = $bonusMalusArray;
        $this->level = $level;
        $this->canUse = $canUse;
    }

    /**
     * Match identical traits, even if atom Uids differ
     *
     * Check if *all* trait values match.
     * This is more expensive than EPAtom's version, but catches duplicate traits with different Uids.
     * @param EPTrait $trait
     * @return bool
     */
    public function match($trait): bool
    {
        if (strcasecmp($trait->getName(),$this->getName()) == 0 &&
            $trait->traitPosNeg===$this->traitPosNeg &&
            $trait->traitEgoMorph===$this->traitEgoMorph &&
            $trait->cpCost===$this->cpCost &&
            $trait->level===$this->level &&
            $trait->canUse===$this->canUse){
                return true;
        }
        return false;
    }

    /**
     * Standard getter to save some comparison operators
     * @return bool
     */
    function isPositive(): bool
    {
        return $this->traitPosNeg == EPTrait::$POSITIVE_TRAIT;
    }

    /**
     * Standard getter to save some comparison operators
     * @return bool
     */
    function isNegative(): bool
    {
        return $this->traitPosNeg == EPTrait::$NEGATIVE_TRAIT;
    }

    /**
     * Standard getter to save some comparison operators
     * @return bool
     */
    function isEgo(): bool
    {
        return $this->traitEgoMorph == EPTrait::$EGO_TRAIT;
    }

    /**
     * Standard getter to save some comparison operators
     * @return bool
     */
    function isMorph(): bool
    {
        return $this->traitEgoMorph == EPTrait::$MORPH_TRAIT;
    }

    /**
     * If this trait allows the use of Psy Chi (Psy I) abilities
     * @return bool
     */
    public function isPsyTrait(): bool
    {
        if ($this->getName() === EPTrait::PSY_CHI_TRAIT_NAME || $this->getName() === EPTrait::PSY_GAMMA_TRAIT_NAME) {
            return true;
        }
        return false;
    }

    /**
     * If this trait allows the use of Psy Gamma (Psy II) abilities
     * @return bool
     */
    public function isPsy2Trait(): bool
    {
        if ($this->getName() === EPTrait::PSY_GAMMA_TRAIT_NAME) {
            return true;
        }
        return false;
    }

    /**
     * Get all positive traits from an array
     * @param EPTrait[] $traitsArray
     * @return EPTrait[]
     */
    static function getPositiveTraits(array $traitsArray): array
    {
        $result = array();
        foreach ($traitsArray as $t) {
            if ($t->isPositive()) {
                array_push($result, $t);
            }
        }
        return $result;
    }

    /**
     * Get all negative traits from an array
     * @param EPTrait[] $traitsArray
     * @return EPTrait[]
     */
    static function getNegativeTraits(array $traitsArray): array
    {
        $result = array();
        foreach ($traitsArray as $t) {
            if ($t->isNegative()) {
                array_push($result, $t);
            }
        }
        return $result;
    }
}
