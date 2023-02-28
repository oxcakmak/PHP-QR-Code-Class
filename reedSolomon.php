<?php

class ReedSolomon {
    private static $singleton;
    private static $GF_EXP;
    private static $GF_LOG;

    public function initTable() {
        self::$GF_EXP = array_fill(0,512,1);
        self::$GF_LOG = array_fill(0,256,0);
        $x = 1;
        for($i = 1; $i < 255; $i++){
            $x <<= 1;
            if ($x & 0x100)
                $x = $x ^ 0x11d;
            self::$GF_EXP[$i] = $x;
            self::$GF_LOG[$x] = $i;
        }
        for($i = 255; $i < 512; $i++){
            self::$GF_EXP[$i] = self::$GF_EXP[$i-255];
        }
    }
    
    /**
     * To get the singleton
     * @return ReedSolomon
     */
    public static function GetInstance(){
        if (self::$singleton == null){
            self::$singleton = new ReedSolomon();
            self::$singleton->initTable();
        }
        return self::$singleton;
    }
    
    /**
     * @param int $x
     * @param int $y
     */
    public function gf_mul($x, $y) {
        if ($x == 0 || $y == 0) return 0;
        return self::$GF_EXP[self::$GF_LOG[$x] + self::$GF_LOG[$y]];
    }
    
    /**
     * Unused method on encoding
     * @param int $x
     * @param int $y
     */
    public function gf_div($x, $y) {
        if ($y == 0) throw new Exception('Zero division');
        if ($x == 0) return 0;
        return self::$GF_EXP[self::$GF_LOG[$x] + 255 - self::$GF_LOG[$y]];
    }
    
    /**
     * Unused method on encoding
     * @param array $p
     * @param array $q
     */
    public function gf_poly_mul($p, $q){
        $r = array_fill(0, count($p) + count($q) - 1, 0);
        
        for ($j = 0; $j < count($q); $j++)
            for ($i = 0; $i < count($p); $i++)
                $r[$i + $j] ^= self::gf_mul($p[$i], $q[$j]);
        return $r;
    }

    /**
     * Unused method on encoding
     * @param array $p
     * @param int $x
     */
    public function gf_poly_scale($p, $x) {
        $r = array(); for($i=0;$i<count($p);$i++) $r[$i]=0;
        
        for ($i = 0; $i < count($p); $i++){
            $r[$i] = self::gf_mul($p[$i], $x);
        }
        return $r;
    }
    
    /**
     * Unused method on encoding
     * @param array $p
     * @param array $q
     */
    public function gf_poly_add($p, $q){
        $r = array(); for($i=0;$i<max(count($p), count($q)); $i++) $r[$i]=0;
        
        for($i=0; $i < count($p); $i++)
            $r[$i + count($r) - count($p)] = $p[$i];
        for($i=0; $i < count($q); $i++)
            $r[$i + count($r) - count($q)] ^= $q[$i];
        return $r;
    }
    
    /**
     * Unused method on encoding
     * @param array $p
     * @param int $x
     */
    public function gf_poly_eval($p, $x){
        $y = $p[0];
        for ($i = 1; $i < count($p); $i++)
            $y = self::gf_mul($y, $x) ^ $p[$i];
        return $y;
    }
    
    /**
     * @param int $nsymb
     */
    public function rs_generator_poly($nsymb){
        $g = array(1);
        for($i = 0; $i < $nsymb; $i++){
            $g = self::gf_poly_mul($g, array(1, self::$GF_EXP[$i]));
        }
        return $g;
    }
    
    /**
     * @param array $msg_in
     * @param int $nsymb
     */
    public function rs_encode_msg($msg_in, $nsymb){
        $gen = self::rs_generator_poly($nsymb);

        $msg_out = array_fill(0, count($msg_in) + $nsymb, 0);
        
        for ($i = 0; $i < count($msg_in); $i++)
            $msg_out[$i] = $msg_in[$i];
            
        for ($i = 0; $i < count($msg_in); $i++){
            $coef = $msg_out[$i];
            if ($coef != 0){
                for($j=0;$j < count($gen); $j++){
                    $msg_out[$i + $j] ^= self::gf_mul($gen[$j], $coef);
                }
            }
        }
        for ($i = 0; $i < count($msg_in); $i++)
             $msg_out[$i] = $msg_in[$i];
        
        return $msg_out;
    }
}
?>
