<?php

use \Bitrix\Main\Localization\Loc;

class CIBlockPropertyCprop
{
    static $arFields = [];
    static $showedСss = false;
    static $showedJs = false;

    function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'C',
            'DESCRIPTION' => Loc::getMessage('IEX_CPROP_DESC'),
            'GetPropertyFieldHtml' => array(__CLASS__,  'GetPropertyFieldHtml'),
            'ConvertToDB' => array(__CLASS__, 'ConvertToDB'),
            'ConvertFromDB' => array(__CLASS__,  'ConvertFromDB'),
            'GetSettingsHTML' =>array(__CLASS__, 'GetSettingsHTML'),
            'PrepareSettings' =>array(__CLASS__, 'PrepareSettings'),
        );
    }


    static function showCss()
    {
        if(!self::$showedСss) {
            self::$showedСss = true;
            ?>
            <style>
                .cl{cursor: pointer;}
                .mf-fields-list{display: none; margin-left: -100px!important; padding: 10px 0;}
                .mf-fields-list.active{display: block;}
                .mf-fields-list td:last-child{padding-left: 5px;}
                .mf-fields-list input[type="text"]{width: 250px!important;}
            </style>
            <?
        }
    }

    static function showJs()
    {
        $showText = Loc::getMessage('IEX_CPROP_SHOW_TEXT');
        $hideText = Loc::getMessage('IEX_CPROP_HIDE_TEXT');

        CJSCore::Init(array("jquery"));
        if(!self::$showedJs) {
            self::$showedJs = true;
            ?>
            <script>
                $(document).on('click', 'a.mf-toggle', function (e) {
                    e.preventDefault();

                    var table = $(this).closest('tr').find('table');
                    $(table).toggleClass('active');
                    if($(table).hasClass('active')){
                        $(this).text('<?=$hideText?>');
                    }
                    else{
                        $(this).text('<?=$showText?>');
                    }
                });
            </script>
            <?
        }
    }


    function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $showText = Loc::getMessage('IEX_CPROP_SHOW_TEXT');

        self::showCss();
        self::showJs();

        if(!empty($arProperty['USER_TYPE_SETTINGS'])){
            $arFields = $arProperty['USER_TYPE_SETTINGS'];
        }
        else{
            return '<span>'.Loc::getMessage('IEX_CPROP_ERROR_INCORRECT_SETTINGS').'</span>';
        }


        $result = '<div><a class="cl mf-toggle">'.$showText.'</a></div><table class="mf-fields-list">';

        foreach ($arFields as $code => $name){
            $v = !empty($value['VALUE'][$code]) ? $value['VALUE'][$code] : '';
            $result .= '<tr>
                    <td align="right">'.$name.': </td>
                    <td><input type="text" value="'.$v.'" name="'.$strHTMLControlName['VALUE'].'['.$code.']"/></td>
                </tr>';
        }

        $result .= '</table>';

        return $result;
    }


    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $btnAdd = Loc::getMessage('IEX_CPROP_SETTING_BTN_ADD');
        $settingsTitle =  Loc::getMessage('IEX_CPROP_SETTINGS_TITLE');

        $arPropertyFields = array(
            'USER_TYPE_SETTINGS_TITLE' => $settingsTitle,
            'HIDE' => array('ROW_COUNT', 'COL_COUNT', 'DEFAULT_VALUE', 'SEARCHABLE', 'SMART_FILTER', 'WITH_DESCRIPTION', 'FILTRABLE', 'MULTIPLE_CNT', 'IS_REQUIRED'),
            'SET' => array('MULTIPLE_CNT' => 1),
        );

        self::showJsForSetting($strHTMLControlName["NAME"]);

        $result = '<tr><td colspan="2">
            <table width="100%" id="many-fields-table">        
                <tr valign="top">
                   <td style="padding-right: 80px; text-align: right;">XML_ID</td>
                   <td style="padding-left: 160px;">'.Loc::getMessage('IEX_CPROP_SETTING_FIELD_TITLE').'</td>
                </tr>';

        if(!empty($arProperty['USER_TYPE_SETTINGS'])){
            foreach ($arProperty['USER_TYPE_SETTINGS'] as $code => $value) {
                $result .= '
                       <tr valign="top">
                           <td style="text-align: right;"><input type="text" class="inp-code" size="25" value="'.$code.'"></td>
                           <td><input type="text" class="inp-title" size="50" name="'.$strHTMLControlName["NAME"].'['.$code.']" value="'.$value.'"></td>
                       </tr>';
            }
        }

        $result .= '
               <tr valign="top">
                    <td style="text-align: right;"><input type="text" class="inp-code" size="25"></td>
                    <td><input type="text" class="inp-title" size="50"></td>
               </tr>
             </table>   
                
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <input type="button" value="'.$btnAdd.'" onclick="addNewRows()">
                    </td>
                </tr>
                </td></tr>';

        return $result;
    }

    static function showJsForSetting($inputName)
    {
        CJSCore::Init(array("jquery"));
        ?>
        <script>
            function addNewRows() {
                $("#many-fields-table").append('' +
                    '<tr valign="top">' +
                    '<td style="text-align: right;"><input type="text" class="inp-code" size="25"></td>' +
                    '<td><input type="text" class="inp-title" size="50" name="<?=$inputName?>"></td>' +
                    '</tr>');
            }

            $(document).on('change', '.inp-code', function() {
                var xmlId = $(this).val();

                if(xmlId.length <= 0){
                    $(this).closest('tr').find('input.inp-title').removeAttr('name');
                }
                else{
                    $(this).closest('tr').find('input.inp-title').attr('name', '<?=$inputName?>[' + xmlId + ']');
                }

            });
        </script>
        <?
    }

    public static function PrepareSettings($arProperty)
    {
        $result = [];

        if(!empty($arProperty['USER_TYPE_SETTINGS'])){
            foreach ($arProperty['USER_TYPE_SETTINGS'] as $code => $value) {
                $result[$code] = $value;
            }
        }

        return$result;
    }

    function ConvertToDB($arProperty, $value)
    {
        $isEmpty = true;

        foreach ($value['VALUE'] as $v){
            if(!empty($v)){
                $isEmpty = false;
                break;
            }
        }

        if($isEmpty === false){
            $arResult['VALUE'] = json_encode($value['VALUE']);
        }
        else{
            $arResult = ['VALUE' => '', 'DESCRIPTION' => ''];
        }

        return $arResult;
    }

    function ConvertFromDB($arProperty, $value)
    {
        $return = array();

        if(!empty($value['VALUE'])){
            $arData = json_decode($value['VALUE'], true);

            foreach ($arData as $code => $val){
                $return['VALUE'][$code] = $val;
            }

        }
        return $return;
    }
}