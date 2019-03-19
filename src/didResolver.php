<?php

/*

Copyright (c) 2017-2019 Mika Tuupola

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

namespace Blockchaininstitute;

use Tuupola\Base58;

class didResolver
{

    /**
     * Create a new Skeleton Instance
     */
    public function __construct()
    {
    }

    /**
     * Friendly welcome
     *
     * @param string $phrase Phrase to return
     *
     * @return string Returns the phrase passed in
     */

    public function resolve_did($mnid)
    {
        echo "didResolver received " . $mnid;
        $return = callRegistry ("uPortProfileIPFS1220", $encodedMNID, $encodedMNID, 'placeHolderCallback');
        echo "resolved did: " . $return;
        return $return;
    }


    private function placeholderCallback ($result) {
        echo $result;
    }

    public function callRegistry ($registrationIdentifier, $issuerId, $subjectId, $callback) {

        $issuer = eaeDecode($issuerId);
        $subject = eaeDecode($subjectId);

        $networks = getNetworks();

        if ( $issuer['network'] !== $subject['network'] ) {
            call_user_func($callback, "Error: Subject and Issuer must be in the same network!");
        }

        if (!$networks[$issuer['network']]) {
          call_user_func($callback, 'Network id ' . $issuer['network'] . ' is not configured');
        } 
        
        $rpcUrl = $networks[$issuer['network']]['registry'];
        $registryAddress = $networks[$issuer['network']]['registry'];

        $functionSignature = '0x447885f0';

        $callString = encodeFunctionCall($functionSignature, $registrationIdentifier, $issuer['address'], $subject['address']);


        // echo "\r\n\r\n" . $callString . "\r\n";

        return $callString



    }

    private function encodeFunctionCall ($functionSignature, $registrationIdentifier, $issuer, $subject) {
        $callString = $functionSignature;

        $regStub = String2Hex($registrationIdentifier);
        $issStub = subStr($issuer, (-1)*(sizeof($issuer) - 3));
        $subStub = subStr($subject, (-1)*(sizeof($issuer) - 3));

        $callString .= pad('0000000000000000000000000000000000000000000000000000000000000000', $regStub, false);
        $callString .= pad('0000000000000000000000000000000000000000000000000000000000000000', $issStub, true);
        $callString .= pad('0000000000000000000000000000000000000000000000000000000000000000', $subStub, true);
        
        return $callString;

    }

    private function pad ($pad, $str, $padLeft) {
        if ( gettype($str) == "undefined" ) {
            return $pad;
        }
        if ( $padLeft ) {
            return substr( ($pad . $str), (-1)*strlen($pad) );
        } else {
            return substr( ($str . $pad), 0, (-1)*strlen($pad) );
        }
    }

    private function String2Hex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }
     

    private function eaeDecode ($payload) {
        $base58 = new Base58([
            "characters" => Base58::IPFS,
            "version" => 0x00
        ]);
        $data = unpack( "C*", $base58->decode($payload) );
        $netLength = sizeof($data) - 24;
        $network = array_slice($data, 1, $netLength - 1);
        $address = array_slice($data, $netLength, 20 + $netLength - 2);
        $network = "0x" . encodeByteArrayToHex($network);
        $address = "0x" . encodeByteArrayToHex($address);
        return [
            "address" => $address,
            "network" => $network
        ];              
    }

    private function getNetworks () {
        return [
              '0x01' => [
                    'registry' => '0xab5c8051b9a1df1aab0149f8b0630848b7ecabf6',
                    'rpcUrl' => 'https://mainnet.infura.io'
              ], 
              '0x02' => [
                    'registry' => '0x41566e3a081f5032bdcad470adb797635ddfe1f0',
                    'rpcUrl' => 'https://ropsten.infura.io'
              ], 
              '0x03' => [
                    'registry' => '0x5f8e9351dc2d238fb878b6ae43aa740d62fc9758',
                    'rpcUrl' => 'https://kovan.infura.io'
              ],
              '0x04' => [
                    'registry' => '0x2cc31912b2b0f3075a87b3640923d45a26cef3ee',
                    'rpcUrl' => 'https://rinkeby.infura.io'
              ]
        ];
    }

    private function encodeByteArrayToHex ($byteArray) {

        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        $hex = bin2hex($bin);

        return $hex;

    }
}

?>