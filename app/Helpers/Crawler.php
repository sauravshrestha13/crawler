<?php

namespace App\Helpers;
use PhpQuery\PhpQuery;

class Crawler{
    protected static $getIdsUrl='https://www.otaus.com.au/search/membersearchdistance';
    protected static $getDetailsUrl='https://www.otaus.com.au/search/getcontacts';
    protected $serviceType;

    static function getIds($name)
    {
        $post = [
            'ServiceType' => 2,
            'Name' => $name
         ];
        $curl = curl_init(Crawler::$getIdsUrl);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response)->mainlist;
    }

    static function getDOM($ids)
    {   
        $url=Crawler::$getDetailsUrl.'?';
        foreach($ids as  $id)
        {
            $url=$url.'ids='.$id.'&';
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if($httpcode==200)
            return $response;
        else 
            return "error";
    }

    static function getContactName($col)
    {
        $pq = new PhpQuery;
        $pq->load_str($col);

        return $pq->innerHtml($pq->query('p strong.name')[0]);
    }

    static function getPracticeName($col)
    {
        $pq = new PhpQuery;
        $pq->load_str($col);

        return $pq->innerHtml($pq->query('.title__tag')[0]);
    }

    static function getAddress($col)
    {
        $pq = new PhpQuery;
        $pq->load_str($col);

        $address = $pq->innerHtml($pq->query('p')[1]);

        $address = preg_replace( "/\r|\n/", "", $address );

        if($address!="")
        {

            $ads = explode("<br>",$address);
            $city = explode(",",$ads[1])[0];

            if(count(explode(",",$ads[1]))>1)
                $state = explode(",",$ads[1])[1];
            else   
                $state = "-";
            
            if(count(explode(",",$ads[1]))>2)
                $post = explode(",",$ads[1])[2];
            else
                $post = "-";
            

            return [
                'street' => trim($ads[0]),
                'city' => $city,
                'state' => trim($state),
                'post' => trim($post),
                'country' => trim($ads[2])
            ];
        }
        else{
            return [
                'street' => '-',
                'city' => '-',
                'state' => '-',
                'post' => '-',
                'country' => '-'
            ];
        }

    }

    static function getPhone($col)
    {
        $pq = new PhpQuery;
        $pq->load_str($col);

        $phone = $pq->innerHtml($pq->query('p')[3]);

        if($phone=="")
            return "-";
        else{
            $pq2 = new PhpQuery;
            $pq2->load_str($phone);
            return $pq2->innerHtml($pq2->query('a')[0]);
        }
    }

    static function getOthers($col)
    {
        $pq = new PhpQuery;
        $pq->load_str($col);

        $items = $pq->query('p');

        $fscheme = [];
        $aop = [];


        $sts= explode("<br>",trim(preg_replace( "/\r|\n/", "", $pq->innerHtml($items[0]) )));

        foreach($sts as $st)
        {
            if(preg_match('/<strong>Funding Scheme\(s\):<\/strong>(.*)/',$st))
            {
                preg_match('/<strong>Funding Scheme\(s\):<\/strong>(.*)/',$st,$fscheme);
            }
            if(preg_match('/<strong>Area\(s\) of Practice:<\/strong>(.*)/',$st))
            {
                preg_match('/<strong>Area\(s\) of Practice:<\/strong>(.*)/',$st,$aop);
            }
        }

        return [
            "funding_scheme" => !empty($fscheme) ? $fscheme[1] : "-",
            "area_of_practice" => !empty($aop) ? $aop[1] : "-"
        ];
    }

  
    static function parseDOM($dom,$f)
    {
        $pq = new PhpQuery;
        $pq->load_str($dom);

        $rows=$pq->query(".results__item .org-main-content .content__row");

        $data=[];

    
        foreach($rows as $row){
            $col_q = new PhpQuery;
            $col_q->load_str($pq->innerHtml($row));

            $col1 = $col_q->innerHtml($col_q->query('.content__col')[0]);
            $col2 = $col_q->innerHtml($col_q->query('.content__col')[1]);

            $c_name = Crawler::getContactName($col1);
            $p_name = Crawler::getPracticeName($col1);
            $address = Crawler::getAddress($col1);
            $phone = Crawler::getPhone($col1);
            $others=Crawler::getOthers($col2);

            $data[]= [ $p_name,
                                $c_name,
                                $address['street'],
                                $address['city'],
                                $address['state'],
                                $address['post'],
                                $address['country'],
                                $phone,
                                $others['funding_scheme'],
                                $others['area_of_practice'],
                    ];
            

            fputcsv($f, [ $p_name,
                        $c_name,
                        $address['street'],
                        $address['city'],
                        $address['state'],
                        $address['post'],
                        $address['country'],
                        $phone,
                        $others['funding_scheme'],
                        $others['area_of_practice'],
             ]);
        }
        return $data;


       
    }


    public static function run($first)
    {
        $name=$first;
        $data=[];
        $all_ids=[];


        $ids=Crawler::getIds($name);
        $all_ids=array_unique(array_merge($all_ids,$ids));

         
        $f = fopen($name.".csv", "w");

        fputcsv($f, [ "Practice Name",
                        "Contact Name",
                        "Street",
                        "City",
                        "State",
                        "Post Code",
                        "Country",
                        "Phone",
                        "Funding Sheme",
                        "Area of Practice"
             ]);


        $dom=Crawler::getDOM($all_ids);
        if($dom != "error")
            $data=array_merge($data,Crawler::parseDOM($dom,$f));
        
          
    }
}