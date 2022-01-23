    <?
    
    // admin relations

    public static function IBlockLangRelations(&$arFields){

        $cases = [
            "PORTFOLIO"=>[
                "RU_IBLOCK_ID"=>8,
                "RU_PROP_LINK_LANG"=>160,
                "RU_PROP_LINK_LANG_URI"=>161,
                "EN_IBLOCK_ID"=>20,
                "EN_PROP_LINK_LANG"=>162,
                "EN_PROP_LINK_LANG_URI"=>163,
            ],
        ];


        foreach($cases as $arCase){

            if($arFields["IBLOCK_ID"] == $arCase["RU_IBLOCK_ID"]){
                BApi::IBlockLangRelationMaster($arFields, $arCase);
            }
            if($arFields["IBLOCK_ID"] == $arCase["EN_IBLOCK_ID"]){
                BApi::IBlockLangRelationSlave($arFields, $arCase);
            }

        }

        return true;

    }

    public static function IBlockLangRelationQuery($iblock = "", $id = ""){

        if(empty($iblock)) { $iblock = -1; }
        if(empty($id)) { $id = -1; }

        $query = BApi::BitrixQuery([
            "FILTER"=>[
                "IBLOCK_ID"=>$iblock,
                "ID"=>$id,
            ],
            "SELECT"=>[
                "ID", "DETAIL_PAGE_URL", "PROPERTY_LINK_LANG", "PROPERTY_LINK_LANG_URI",
            ],
            "FAST_MODE"=>true,
            "RESULT_ARRAY"=>false,
        ]);

        return $query;

    }

    public static function IBlockLangRelationMaster(&$arFields, $data){

        $thisID = $arFields["ID"];
        $linkID = BApi::GetArrayFirst($arFields["PROPERTY_VALUES"][$data["RU_PROP_LINK_LANG"]],"value");
        $linkID = $linkID["VALUE"];

        $rus = BApi::IBlockLangRelationQuery($data["RU_IBLOCK_ID"], $thisID);
        $eng = BApi::IBlockLangRelationQuery($data["EN_IBLOCK_ID"], $linkID);

        if(!empty($eng["DETAIL_PAGE_URL"])){

            CIBlockElement::SetPropertyValuesEx($rus["ID"], $data["RU_IBLOCK_ID"], [
                "LINK_LANG_URI"=>$eng["DETAIL_PAGE_URL"],
            ]);

            CIBlockElement::SetPropertyValuesEx($eng["ID"], $data["EN_IBLOCK_ID"], [
                "LINK_LANG"=>$rus["ID"],
                "LINK_LANG_URI"=>$rus["DETAIL_PAGE_URL"],
            ]);

        } else {

            CIBlockElement::SetPropertyValuesEx($rus["ID"], $data["RU_IBLOCK_ID"], [
                "LINK_LANG"=>"",
                "LINK_LANG_URI"=>"",
            ]);

            $query = BApi::BitrixQuery([
                "FILTER"=>[
                    "IBLOCK_ID"=>$data["EN_IBLOCK_ID"],
                    "=PROPERTY_LINK_LANG_URI"=>$rus["DETAIL_PAGE_URL"],
                ],
                "SELECT"=>[
                    "ID",
                ],
                "FAST_MODE"=>true,
            ]);

            foreach($query as $item){


                CIBlockElement::SetPropertyValuesEx($item["ID"], $data["EN_IBLOCK_ID"], [
                    "LINK_LANG"=>"",
                    "LINK_LANG_URI"=>"",
                ]);

            }

            unset($query);

        }

    }

    public static function IBlockLangRelationSlave(&$arFields, $data){

        $thisID = $arFields["ID"];

        $eng = BApi::IBlockLangRelationQuery($data["EN_IBLOCK_ID"], $thisID);

        if(!empty($eng["PROPERTY_LINK_LANG_VALUE"])){

            $query = BApi::BitrixQuery([
                "FILTER"=>[
                    "IBLOCK_ID"=>$data["RU_IBLOCK_ID"],
                    "=PROPERTY_LINK_LANG"=>$thisID,
                ],
                "SELECT"=>[
                    "ID",
                ],
                "DEBUG"=>true,
                "FAST_MODE"=>true,
            ]);

            foreach($query as $item){

                CIBlockElement::SetPropertyValuesEx($item["ID"], $data["RU_IBLOCK_ID"], [
                    "LINK_LANG_URI"=>$eng["~DETAIL_PAGE_URL"],
                ]);

            }

            unset($query);

        }



    }
