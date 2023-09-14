<?php
class Twubric{
    use GetProperty;
    private $criteria;
    private float $points;

    private $maxWeight = [
        'Friends'=> 2,
        'Influence'=> 4,
        'Chirpiness'=> 4
    ];
    public function __construct($json)
    {
        $this->criteria = [];
        $obj = json_decode($json,true);

        $selfCalculated = 0;
        foreach ($obj as $key=>$value){
            if(ucfirst($key) != 'Total'){
                $selfCalculated += $value;
                $this->criteria[] = new Criteria($key,$value ,$this->maxWeight[ucfirst($key)]);
            }else{
                $this->set('points',$value);
            }
        }
        if($this->get('points') != $selfCalculated){
            $this->set('points', $selfCalculated);
        }
        return $this;
    }

}
class Criteria{
    use GetProperty;
    private String $label;
    private float $value;
    private float $maxValue;
    private Scale $scale;


    public function __construct($label,$value,$maxWeight)
    {
        $this->set('label',$label)
            ->set('value',floatval($value))
            ->set('maxValue',$maxWeight)
            ->set('scale', (new Scale($value/$maxWeight)));

        return $this;
    }

}

class Scale {
    use GetProperty;
    private $attribute;
//    private double $max;

    public function __construct($max)
    {

//        $this->set('max',$max);
        $scaleAttributes = $this->populateScaleAttributes();

        if(stripos($max,'%') !==false){
            $max = (str_ireplace('%','',$max)/100);
        }
        foreach ($scaleAttributes as $scaleAttribute){
            if($max <= $scaleAttribute->get('upperRange') && $max >= $scaleAttribute->get('lowerRange')){
                $this->set('attribute',$scaleAttribute->get('label'));
                break;
            }
        }
        return $this;
    }
    private function populateScaleAttributes(){
        $scaleAttributes = [];

        $scaleAttributes[] = new ScaleAttribute('High',1,0.67);
        $scaleAttributes[] = new ScaleAttribute('Average',0.66,0.34);
        $scaleAttributes[] = new ScaleAttribute('Low',0.33,0);

        return $scaleAttributes;
    }
}
class ScaleAttribute {
    use GetProperty;
    private $lowerRange;
    private $upperRange;
    private $label;

    public function __construct(String $label,float $upperRange,float $lowerRange)
    {
            $this->set('label',$label)
                ->set('upperRange',$upperRange)
                ->set('lowerRange',$lowerRange);

            return $this;
    }

}

trait GetProperty{
    public function get($property){
        return $this->{$property};
    }
    public function set($property,$value){
        $this->{$property} = $value;
        return $this;
    }
}
