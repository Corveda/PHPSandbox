<?php
    require_once('../vendor/autoload.php');

    if(isset($_REQUEST['load'])){
        header('Content-type: text/html');
        die('<html><body><form id="load_form" action="./" method="POST" enctype="multipart/form-data"><input type="file" name="load" onchange="javascript:document.getElementById(\'load_form\').submit();"/></form></body></html>');
    }
    if(isset($_FILES['load'])){
        if($_FILES['load']['error'] == 0 && ($data = file_get_contents($_FILES['load']['tmp_name']))){
            header('Content-type: text/html');
            die('<html><body><script>window.parent.load_template(\'' . addslashes($data) . '\');window.location.href=\'./?load=true\';</script></body></html>');
        }
        header('Content-type: text/html');
        die('<html><body><script>alert("Template could not be loaded!");</script></body></html>');
    }

    if(isset($_POST['save']) || isset($_POST['download'])){
        if(isset($_POST['download'])){
            $data = json_decode($_POST['download'], 1);
            if(!is_array($data)){
                header('Content-type: text/html');
                die('<html><body><script>alert("Template could not be saved!");</script></body></html>');
            }
            $code = $data['code'];
            $setup_code = isset($data['setup_code']) ? $data['setup_code'] : null;
            $prepend_code = isset($data['prepend_code']) ? $data['prepend_code'] : null;
            $append_code = isset($data['append_code']) ? $data['append_code'] : null;
            $options = isset($data['options']) ? $data['options'] : array();
            $whitelist = isset($data['whitelist']) ? $data['whitelist'] : null;
            $blacklist = isset($data['blacklist']) ? $data['blacklist'] : null;
            $definitions = isset($data['definitions']) ? $data['definitions'] : null;
            $filename = $template = stripslashes($data['save']);
        } else {
            $code = $_POST['code'];
            $setup_code = isset($_POST['setup_code']) ? $_POST['setup_code'] : null;
            $prepend_code = isset($_POST['prepend_code']) ? $_POST['prepend_code'] : null;
            $append_code = isset($_POST['append_code']) ? $_POST['append_code'] : null;
            $options = isset($_POST['options']) ? $_POST['options'] : array();
            $whitelist = isset($_POST['whitelist']) ? $_POST['whitelist'] : null;
            $blacklist = isset($_POST['blacklist']) ? $_POST['blacklist'] : null;
            $definitions = isset($_POST['definitions']) ? $_POST['definitions'] : null;
            $template = stripslashes(isset($_POST['save']) ? $_POST['save'] : $_POST['download']);
            $filename = isset($_POST['download']) ? $template : trim(preg_replace('/[^a-zA-Z0-9_ ]/', '_', $template), '_');
        }
        if(!$filename){
            header('Content-type: text/html');
            die(json_encode(array(
                'message' => 'The template could not be saved because the requested template name was invalid. Please rename your template and try again.',
                'success' => false
            )));
        }
        $cnt = '001';
        if(isset($_POST['download'])){
            $filename = $filename . '.json';
        } else {
            $cnt = count(glob('templates/*.json')) + 1;
            $filename = str_pad($cnt, 3, '0', STR_PAD_LEFT) . ' - ' . $filename . '.json';
        }
        if(!isset($_POST['download']) && file_exists('templates/' . $filename)){
            header('Content-type: text/html');
            die(json_encode(array(
                'message' => 'The template could not be saved because the another template already exists with the same name. Please rename your template and try again.',
                'success' => false
            )));
        }
        $data = array(
            'code' => $code,
            'setup_code' => $setup_code,
            'prepend_code' => $prepend_code,
            'append_code' => $append_code,
            'options' => null,
            'whitelist' => $whitelist,
            'blacklist' => $blacklist,
            'definitions' => $definitions
        );
        if(count($options)){
            $sandbox = new \PHPSandbox\PHPSandbox;
            foreach($options as $name => $value){
                if(($name == 'error_level' && $value != error_reporting()) || ($name != 'error_level' && $sandbox->get_option($name) != $value)){ //save unique options only
                    $data['options'][$name] = $value;
                }
            }
        }
        if(isset($_POST['download'])){
            header('Content-disposition: attachment; filename="' . $filename . '";');
            header('Content-type: application/json');
            die(json_encode($data));
        } else if(file_put_contents('templates/' . $filename, json_encode($data))){
            header('Content-type: text/html');
            die(json_encode(array(
                'message' => 'The template "' . $template . '" was saved successfully!',
                'name' => $cnt . ' - ' . $template,
                'file' => $filename,
                'success' => true
            )));
        }
        header('Content-type: text/html');
        die(json_encode(array(
            'message' => 'An error occurred that prevented your template from being saved!',
            'success' => false
        )));
    }

    if(isset($_POST['code'])){
        $code = $_POST['code'];
        $setup_code = isset($_POST['setup_code']) ? $_POST['setup_code'] : null;
        $prepend_code = isset($_POST['prepend_code']) ? $_POST['prepend_code'] : null;
        $append_code = isset($_POST['append_code']) ? $_POST['append_code'] : null;
        $options = isset($_POST['options']) ? $_POST['options'] : array();
        $whitelist = isset($_POST['whitelist']) ? $_POST['whitelist'] : null;
        $blacklist = isset($_POST['blacklist']) ? $_POST['blacklist'] : null;
        $definitions = isset($_POST['definitions']) ? $_POST['definitions'] : null;
        $sandbox = \PHPSandbox\PHPSandbox::create()->import(array(
            'setup_code' => $setup_code,
            'prepend_code' => $prepend_code,
            'append_code' => $append_code,
            'options' => $options,
            'whitelist' => $whitelist,
            'blacklist' => $blacklist,
            'definitions' => $definitions
        ));
        try {
            ob_start();
            if($setup_code){
                @eval($setup_code);
            }
            $result = $sandbox->execute($code);
            if($result !== null){
                echo (ob_get_contents() ? '<hr class="hr"/>' : '') . '<h3>The sandbox returned this value:</h3>';
                var_dump($result);
            }
            echo '<hr class="hr"/>Preparation time: ' . round($sandbox->get_prepared_time()*1000, 2) .
                ' ms, execution time: ' . round($sandbox->get_execution_time()*1000, 2) .
                ' ms, total time: ' . round($sandbox->get_prepared_time()*1000, 2) . ' ms';
            $buffer = ob_get_contents();
            ob_end_clean();
            die('<pre>' . $buffer . '</pre>');
        } catch(\PHPSandbox\Error $error){
            throw $error;
        }
    }

    if(isset($_GET['template'])){
        $template = stripslashes($_GET['template']);
        if(file_exists($template)){
            header('Content-type: text/html');
            readfile($template);
        }
        exit;
    }

    if(isset($_REQUEST['download'])){
        exit;
    }

    $data = json_decode(file_get_contents("templates/001 - Hello World.json"), 1);
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<head>
    <title>PHPSandbox - Toolkit</title>
    <link href="http://code.jquery.com/ui/1.10.1/themes/smoothness/jquery-ui.css" type="text/css" rel="stylesheet"/>
    <script src="http://code.jquery.com/jquery-1.9.1.js" type="text/javascript" charset="utf-8"></script>
    <script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript" charset="utf-8">
        var JSON;JSON||(JSON={});
        (function(){function k(a){return a<10?"0"+a:a}function o(a){p.lastIndex=0;return p.test(a)?'"'+a.replace(p,function(a){var c=r[a];return typeof c==="string"?c:"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+a+'"'}function l(a,j){var c,d,h,m,g=e,f,b=j[a];b&&typeof b==="object"&&typeof b.toJSON==="function"&&(b=b.toJSON(a));typeof i==="function"&&(b=i.call(j,a,b));switch(typeof b){case "string":return o(b);case "number":return isFinite(b)?String(b):"null";case "boolean":case "null":return String(b);case "object":if(!b)return"null";
            e+=n;f=[];if(Object.prototype.toString.apply(b)==="[object Array]"){m=b.length;for(c=0;c<m;c+=1)f[c]=l(c,b)||"null";h=f.length===0?"[]":e?"[\n"+e+f.join(",\n"+e)+"\n"+g+"]":"["+f.join(",")+"]";e=g;return h}if(i&&typeof i==="object"){m=i.length;for(c=0;c<m;c+=1)typeof i[c]==="string"&&(d=i[c],(h=l(d,b))&&f.push(o(d)+(e?": ":":")+h))}else for(d in b)Object.prototype.hasOwnProperty.call(b,d)&&(h=l(d,b))&&f.push(o(d)+(e?": ":":")+h);h=f.length===0?"{}":e?"{\n"+e+f.join(",\n"+e)+"\n"+g+"}":"{"+f.join(",")+
                    "}";e=g;return h}}if(typeof Date.prototype.toJSON!=="function")Date.prototype.toJSON=function(){return isFinite(this.valueOf())?this.getUTCFullYear()+"-"+k(this.getUTCMonth()+1)+"-"+k(this.getUTCDate())+"T"+k(this.getUTCHours())+":"+k(this.getUTCMinutes())+":"+k(this.getUTCSeconds())+"Z":null},String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(){return this.valueOf()};var q=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
                p=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,e,n,r={"\u0008":"\\b","\t":"\\t","\n":"\\n","\u000c":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},i;if(typeof JSON.stringify!=="function")JSON.stringify=function(a,j,c){var d;n=e="";if(typeof c==="number")for(d=0;d<c;d+=1)n+=" ";else typeof c==="string"&&(n=c);if((i=j)&&typeof j!=="function"&&(typeof j!=="object"||typeof j.length!=="number"))throw Error("JSON.stringify");return l("",
                {"":a})};if(typeof JSON.parse!=="function")JSON.parse=function(a,e){function c(a,d){var g,f,b=a[d];if(b&&typeof b==="object")for(g in b)Object.prototype.hasOwnProperty.call(b,g)&&(f=c(b,g),f!==void 0?b[g]=f:delete b[g]);return e.call(a,d,b)}var d,a=String(a);q.lastIndex=0;q.test(a)&&(a=a.replace(q,function(a){return"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)}));if(/^[\],:{}\s]*$/.test(a.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
                "]").replace(/(?:^|:|,)(?:\s*\[)+/g,"")))return d=eval("("+a+")"),typeof e==="function"?c({"":d},""):d;throw new SyntaxError("JSON.parse");}})();
    </script>
    <style type="text/css" media="screen">
        #body {
            margin: 0;
            font-family: "Trebuchet MS", sans, sans-serif;
            font-size: 14px;
        }
        #instructions {
            position: absolute;
            top: 0;
            right: 303px;
            left: 0;
            padding: 0 2em;
            height: 60px;
        }
        #toolbar {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 263px;
            padding: 20px;
            border-left: 1px solid gray;
        }
        #output_container {
            position: absolute;
            top: 360px;
            right: 303px;
            left: 0;
            bottom: 0;
            padding: 1em 2em;
        }
        #output {
            height: 70%;
            overflow: auto;
            padding: 1em;
            font-size: 12px;
        }
        #editor {
            position: absolute;
            top: 60px;
            right: 303px;
            left: 0;
            height: 300px;
            border: 1px solid gray;
            border-left: 0;
        }
        .hr {
            border: 1px solid transparent;
            border-top: 1px dashed gray;
        }
        #configuration_container {
            height: 360px;
        }
        #configuration {
            font-size: 11px;
        }
        #templates {
            width: 100%;
            font-size: 12px;
        }
        #toolbar label {
            font-weight: bold;
            margin: .5em;
        }
        input.whitelist, input.blacklist, input.func, input.var, input.superglobal, input.const, input.magic_const, input.namespace, input.alias, input.class, input.interface, input.trait {
            width: 100%;
            text-align: left;
        }
        #mode_container {
            float: right;
            margin-top: 1em;
        }
        #func_editor_dialog, #var_editor_dialog, #superglobal_editor_dialog, #const_editor_dialog,
        #magic_const_editor_dialog, #namespace_editor_dialog, #alias_editor_dialog, #class_editor_dialog,
        #interface_editor_dialog, #trait_editor_dialog, #help_dialog {
            display: none;
            font-size: 12px;
        }
        #func_editor {
            border: 1px solid gray;
        }
        #func_editor_name, #func_editor_args {
            font-family: "Courier New", serif;
            font-size: 10px;
        }
        .ui-dialog-titlebar {
            font-size: 12px;
        }
        .ui-dialog-content {
            padding-left: 0.7em !important;
        }
        .ui-dialog-buttonset {
            width: 100%;
            font-size: 12px;
            font-weight: bold;
        }
        .ui-dialog-buttonset .ui-button:last-child {
            float: left;
            margin-left: 1em;
        }
        .ui-dialog-buttonset .ui-button:first-child {
            float: right;
        }
        #download, #download_form, #load {
            display: none;
        }
    </style>
    <script type="text/javascript" >
        var code = <?php echo json_encode(isset($data['code']) ? $data['code'] : ''); ?>,
            setup_code = <?php echo json_encode(isset($data['setup_code']) ? $data['setup_code'] : ''); ?>,
            prepend_code = <?php echo json_encode(isset($data['prepend_code']) ? $data['prepend_code'] : ''); ?>,
            append_code = <?php echo json_encode(isset($data['append_code']) ? $data['append_code'] : ''); ?>,
            current_mode = 'code';
    </script>
</head>
<body id="body">
<div id="instructions">
    <span id="mode_container">
        <label for="mode"><strong>Editor Mode:</strong></label>
        <select id="mode">
            <option value="code">Sandboxed Code</option>
            <optgroup label="Trusted Code">
                <option value="setup_code">Setup Code (runs before code and outside sandbox)</option>
                <option value="prepend_code">Prepended Code (runs before code inside sandbox)</option>
                <option value="append_code">Appended Code (runs after code inside sandbox)</option>
            </optgroup>
        </select>
    </span>
    <h2>Code Editor</h2>
</div>
<div id="toolbar">
    <a href="https://github.com/fieryprophet/php-sandbox" target="_blank"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQYAAAAyCAYAAAC+qXUzAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAP2JJREFUeNrsvQmQXdd5HnjO3e/be1+BbjQAghsIiBSoxaRk7bZlSrIsJ7LkjJ1YyTiTmqqkKqnUVFJTM1UzU5NxKjU1lXGSSpyRl1iyZImSTImkSJESKYoACRAg9rX3Rne/16/ffvd7zvz/ue++flsTgKKpiZQ+rMduvL733LP9///961UYYwQbpXQKfjwOHxk+nNx927Zt+xTcb/X7I+dRV41GQ3zgOvEdfsIwJJ7nEdd1xWdycpLIstz6ezy2fg2vX1lZEX1gw/sOHz7cuj9+9jv1gc9eWFggQ0NDZGxsTHyH18f330uL7/N9X/QLayJ+ZjIZkk6nxd8lSWp9mmves07x7/jB6+LfNzc3SalUat0b359MJltrGs+9/b64BUEgfuJ64RjxU6vVyNbWFjl+/DjRNE2MEb/vN3/8m2VZJJFIEMMwOvYxXud4L3BvsJ9qtUoKhYL4t6qqYn/wJ34URRHjbF+DbDYr1qt7z95pH/Fva2trok88P++0h/isfD5PisViax3xOrxX13WxRjjWeP9w/DgPXNeDBw+21hd/4gf7a//Ez477jdcD+8DvsX/HccQH+z906JBYh3gt4z2K78e/nThxorWf99hU6Pc98BMXJbzLe3ASNsz7vCIOEgwMJvHo17/97O+vrucziizf1Sh8mMijR++/uH9iZM31vOu7EUz7Ydpre22v3V1DxlKpVMjAwMA9MwagtYHtjaVPLV86+T5gLCyi+XdunDOq6MmSPnr4y4qQZMD9gKsZ/+bLXz/2xsVb06ah39XDEQH8/mc/7Hz+0x9T6pbdd2K7SYG9ttf22jsStmAGV65cIffffz8ZHBzsQBR3aoA29M2lq2NbZ77+xOhghtwNX+EsJFdLUsF4+Df/ShFwJ4I9LJVKuIODA8TQ744xmLpKMulkMQjDHq6Ak0CmMDMz89NCob221/6rZw4oUK9evSrUsHthDpKiyCwMlUw2A3Q4QMI70B8qViELiOqQGuPckfDBjDNUCzTX89UI7fO7+uC1mqo4QPR+u76Jg0eUMDs726Hz77W9ttfuraFQRRq9ePGisNfEzOLOH6IEvqcjObM22tztw0jTTkRknzFuR4gBvgyDQIbfZfoOuggXf2szkkVcLWy2FlLI5XJkbm6uNam9ttf22n8ec0AD5q1bt+5aLQeJr4W+m9QViShA1YzHdgREBnwXVQKYA1Ud4AFWizEAYlD8IJR34wscLb2hxyTaxAvwP5mLe6nrejy2RA8PDwsL7h5T2Gt77WfX0EOBHiT8oDGy3fvRlzEEoQZCWtvcqrOSQwW9wj08bWp0MGNIfRQXoTkEVHMpJ7aCEp5zmQa+r/l+oPTzHAhGELrB737qA398YN9E3vMDOXIhcdAT2LXNfL6G7i50F8VunT2msNf22s/W3oA0t7i4KFzL+HtMq/2YAwjrbZaZ+W5x7GNvFClleM3Uvv3Vyu0zT6WcxV/SzUTPfSGqElRpUMasJmMQdgEJ7qa76BBEkSQ2PT66MpDL/t/AGDxJinSdarVmT01NcbQnoL8eEcgeU9hre+1n31Dglstlsry8LBA5MgiMY4kZRRehL1uu/0daMisDbfMhQPIPPvKuR68XL36WW31iPJAxgAIQcmIBSvBixiD5HiIGX6NU6sMXOOopPmAEBxhBeWBwULhQYkMIDi5CEKEwSN4pXiGCNf25YvvvkkQ7jRq7XPtOXPZe+oivxzW+V4YOPJXfW1wYPEtWklSSs7DmaR4xZR71xUFzC2swFhfW12eBX/2vIQZk1zlGAUT99lF813FfW7BRv/5/3texXUXHICkMGTBNUwhlDNJqZxAwU8f3XKF6PPTAA4D6A8O3G2YU3MV7VjJgHFADtbi3wxjQxiCDakB3I2RZltwwCB1iUOnIkSMskUgcBHTwEfgoruMwfk9ExANVVRZMQ78BKswKEYZTLiLCcHKweZPw70/C2FT8W7/DAH+7BT9fhWud9kjHtoW5Yx8wdnz2j6CPOvzUYME/HgbBLAwmuKfdQqKmpGTo+lV45qLneeV3OP2SpGiH4dkPl7fWH7RKm6O+XZ2BZ8t0hxFzRUusKJpZl1TNTg9PX4frfwjjXO187C+WtwcjDvtELYp99HxfRcN591qCaKrCHr+oquoGfJMEjvoJUIknGEqprpXHMwzfn4Z1PN8dqfjzxkDjD46/Xq+3olLbGUQcR/Tggw8Kge26LtBe2DfWCb/yAS6EXKoDA3F3EEPga0EY9kUMeFRlSfJDuAHWW+yaZdvH/uc//Df//YXrC/t0gDJ3T0NCNwoPTI/f/NgHHn/ricff9YyqKK9YjNVwcgiXVEU+9K//+Ct/+9kfnnowYRo9TAqQDvn1Dz3+tYfum7nief5qHD46PT0tfuK/U8nE1Je/+u0v/tk3nj2eSiX76WDkE08cf/bY/XM3AIpdn5qcGHjp1VNP/ct/+2eflxWV34tkwSVJp8ziiWP3X/3kR594cXJs9Dlgcle6JRSggwzwkF9fu3b6Vyrzpx6X7c19CvN1U+Wy3PU4J6DEBQ0vZDy8RUcv+cPHq3AIWowBD0QqlRIH4RehoRRDCYh7hwe8ZXTTzKnlsy98sTH/2nHNSLQCdXBZWeiTbT54qZE6tDY7M7MxMaWMrlz88ee2L37/k6Zp8HbNGEiIVFxpuzH06L8IOb0IGjuL3eoTExM/92uHrZ1B4NnAue3fv1+sKToHgiA0mO9qkin1P8fITOBSkLq+0oyNp47tyLvZGHAzFEXAWgtUDo73wIEP1vNFc3WrkTV0/54ns7h++fGXT50/8dH3HXv4733x04OKLH+rUqnUBGIgapgvlrTVQi2bTPYwfuI7DYx357VKznf9oBXvjuOKmQ/6T4ulsrqyVctm3F7+6EIf+cIWqU4O+agaifDTao2tbJYzqpkk9wY4KVnbrmffvr48972XTz72B1/81Ox7Hzv6H+Ggv436X3TQaRaQ1e8sv/md39W3L7x7OmdQYxiYHjWbbuBeDg6HV4HjqxQWi4NlpazKstLEE1G8/oEDB36hvD/IRJE54NxiBs+IJzu1QmImx7JmYgcl4bW+G5CF1bpcC2u1ccdGOxl3G3UyarqZ0SETjeOttcRDfHqpVt8qbtcURQWm4AvCQYN5bNj7RWCuMYPAD549/A6RGM4RGAMFRNU3IgEZrR8SFjDJon5TlQC1WimWKjreSCWlr5QHwvXhADpBMwkHVXdFkUNdVTDI6Z4noWmqYEjPvXbuCcu2pC99/qmq6zrfBX0okAEgQv9M69M3EpHM1TBhaFWAly6MuWWhbWcMTeYFfah9xgerECpE19QGzN/FNYju5RyvBTWH3LsmKhMMJS/W3bH/499+5ff/8Zd8a//UaAUO+SIcQJlT6VcWTz79e7n6pXePjw8hZ46i0TjZzQTSPNAi6UoCxqU1NSDBxDDxC/NP7iVM9ueFOaDejHOTJBkWCQQWEDGhCjAJicTmHwnWwgslEhKlzMLQxnXwMFlJnKroWkb4DroA5ulxtQrfVFB6ZrMZAbGRAeG/f5FsOLGwwDyLONFOIG0AApyFkphrnyMXMsJ8LllS4LsSLmgQBnKjYal8N6+EUCUoXAp4ARZf3NO0TbzDDnd+ukmNR3Iykx0gr7996/2vvH76k4HvT1SqVTz4XPRNdxsL4aahNvwgdJBI8BOPqfXxff5O44PF4QDfXQ9nhNmGzfveCRX0zqmXgWI4uU+15Fe+84PPlUqlE2KdqLxv8+a5X0vWrrx7fGRIGHm6x4a9IUNUZEl88HfUoNH7o0ksAUufhr5ozPwwXuQXNaIUD3WUnYrwN6BA+FLPWYB/e3CSA0aqsNdOc9+V0Pc0ucdMGSECK1As3w/LmUyaPPTQQ4Ip/KJ60CKEEIgM5GhtMKM2wPACaTchBIghcENaR0IXiEGWJKVar2twYKks97cLADJAVcKOGQMiBr5bBBX8FzgORkXFQk5wcBWIRur2BCChqzp54/z1E/vHhw7purGSTacVoFi5v70DCAjUGFWRYJM9ADCRsQr1RPyJsEno6K6rWLYjSbvEZaDVSlXkBnIFsXCev/shwexTWFjmey1mhVOXZJWoaF/pIFAujD+r+fLBS9duPTo6OvY9x7EP1lfOPzqXMfrGrEdrGZLtikMcL1KdNEWCNZdJJqESXWYpGGYSUAYwcz+cmpoS6kn3eKkkGcBKNJAYOrDddBtnhJWUXM5ZDW6qdxtj785aT7ufpcP+ZOE5Kc6ioBbAaTXOQ+zfveeDLMkJ6DMFsilFeQSlYHdtifkJ5jsKzKqD2sVBDjgyhkbIItQX+AEgq0Dpt+d4TpxQrk3PzDlHDh8YAzSShO84gBIqcerBHm6DyLR+akIU45dz0I8RaXvwPxrYsK8VGKn9szI4tqfd36kh4yttb4t0/XQ6A4zB0wgPhB2R96FxoH9B44HnhcLGANJJtm1H44z31T+EJNQUF/QTCwkPP7D4UtBXXwGK85zwlx6e/Nr02PAmei2gBWtb1X1vXl7+uM+VdPfGKQD3N4uVqVK5Mjc+Pv6y4zhqveGofYkaCVKiTuCHtkc9hpIedW2UoKhXIQSNXDmOVq3W1N0WUhaMQbJhLq6ooeD7FD5SP1YHB46MpqQrTx5/+HmYtgySnMOBVN+6svz+xUL9GNo3ukkIWefS6sZhIORJq1aZlt3iqJbVerLccI7VhkPOLls31pzUGaYO5mVZYSk1GNCYNZJR/IPU90ZA9RvxQ1NnYWChYYlGmHEAnnYfrPEQI3TUrZaH3fp2FvpMBk5tf0eNByJVJC2xrKaHl6isXKVheAm+rkVEIxAXegQ/CP0cAhoPuiYUu1AvwvQXiaS+q1EtHnMqhf2hZ03xqGHO7rKSHFqS9ORZKrELcGflnQ+7pMJY5jDl3y7lZzyrMh66jekmXyBcVjdkPbXOAmTwvfuITNYPuePzwG/uoRwGgdp9Ka6VB3uYzg4Xsgb5UHn12kHPaYzEB4pJSlExMvNqavAmldVzIHeWke/cBTPIwgfGHzxYL67PhHZ1xnetoSbNgMTTNqBfWJPcLVDRL4Bmi8Zjux3yNxnyQbjjSSA/uhvMDUK+CXTxCvy5gclUSDYwgF8GYTHDWW+9BSFsOFsGdv0TYAxOIpEkvucpIIFkugtd+VgeBdCXDLK2yRgkkK62xvku4B3+oCmyAx0745NTeD1KZAyckLo5A84KhJ1/cN/YuXQy9R9BTQlBgvIPPvHew4O5N/k3Xjr9OdNM9OhEbsCy9UZ9BF0qfuCLvvvBdSIMb9RD+Ihy+8CBOYEWYhUCDzlKbAznxAhNulsfEvVhnDWQUj7q68AfJGASfQtRwBzIxNBQfmp8+C9dP7hGBEIZN9//+GOf/F/+6Cv/W9Vhw7IsdWMSUm/Yo4AWcrChIO9CkxKVtIMsAaZgV6+u28vX3Kn/U9XNF2WJFyRgNK6mD9icDDYkadzztx+q1aw1zaB+LpclIyMjsImhyXz3d8uLZ58Kavmcwuwpjbumyj0da8HkVK62zxwYGWn41LG5UXWM8bPy6P3fAcjzPVivRQyayeYGTLdW/Gj58ve/lABybT8KuIQVy7PZvg/8S8Dpv1a68tKvatbaAyZ1UwBoWhjT8khocbVWU0bepqMPP02o+m24dbGvHiwrsyDBPlFduvDLQeHaY0ZQHtdpYCY0osRPdoA0rVCuDpuKRmW9J/YFmALzAm4BS/eadiIFkJ0u0ygEOCYxJlCcSaaUwsfo4rO/ZshhItdmRrIBeTiBXK/R1O0gPfuaOnTgGSD4l+BP5V04GoxHe8yulz5Su335/bS8+IAW1oYTcpgaUNo9S4RboQTiKrXup/adUYfmXpYUHd3ONxDt4boj8gPk8vDWlR/9Xd3dOKJpeidrEFlQASnyoTfK6kQN9KofY1EhI5F6V2nx7D/wV996QlbUHi2+0nDC2+HYNyqefBOE5XKkhnqwiCFcrHYIqDjRygs5hiTYGJMjGAPotEAUvsxbmZM94gK/R+nq06ioC24CZQIxdN2DRAfqDcrhBm1sjY2NiwCLdCa7OD05uk1Rveh+RpR8wR3QK10Pqzl5mg2oYSfTs/1ShupHAP+zgSlwhNVxAlesVwlCkGWtbtlN6cF7GB1wNDRc2phujvfB3CgggzjItGf+GP9Rqzdqmm6UJuGZQKClXC63NZBJlkuNMjAGtTdgBMZiWXYIalEpZFI9ZCzN28RZU6UhhkqNibHhMJ0dWg5810HkA/eWAPDNUxAMoaS/CADJRbwRz5eFPFteufZQqvDmhyeHMwI2yhKMgeo7p6PtOaiygYg0ANoaldriJ5bnVx6spo8lQTP5k2KxmE+mM7TRqPEBuTY2N5pBP+kOuoIhX1ixi4WtpfudzZf/5kyaDSZHEuJZvO05A0SEyOcajc0P3lrcPFRJHwfyk/8E1nerkymox2rbG3/LuvnD3xiRtucG0ibRNLNlx+EtxUVQd4Y3D273AgNTYE5ALWAJLqLYmDEgq2JCoeU7KBMmMTei59rtRfFzBqIDmAp8776t8rn7loq3joajjwEsk77djXoA5aRAAXmqcOPU56X8uQ+MJ4JcJmfC+if6jR85VBpU9nSlfum+9VvXnqykjjwiD+z/cziDb966dYuhi11WtFO+MfLigDt/4tCwqobtJEWF6kwurix+8LalvZDKDp6E05uC9fs0Wz35kftHtDQFER9fj37vesMi5xesN1d982XCw/XxsVGBCDEWBICyEqv73XTlBhx1cwdhc6xKSAjDhL2P97cawIYLxtDcALTk6sCnNYqZ3Lw76IZ7IQutfdP7yL79+zEyK5FMmO+eX759P++6njSlJkjvOnRfwYQseIYCBzNCI7zbMMWJqsuVg3Nzxv7p6d+G50hoqGvXgXE34IBkMFahXx+ROkJcgIBoM+HRnDQFmJGB/feugfAE2DAP9/B9R4SbK5NO7V9eWz9eKFXH0L3WbewSzISSyvZ2sZIdHJp3SXrJcSsTqOq0owZkObPD2mhp/frfZukTJTOZegGIvISW+dheAvNxcI0wZgEzVzEQjMhqwncbmUzSJIYZueZ43CPfCcIU1vkOopJINpMm9+vOvteXz36hymfO5wayz/ueL1QmYOuAWiUlbM+ihV9NTdZH7Sv/zcGJVFIGRIMG1Ejs9BJsIpkiRxR7qrLy9u8U1cPXQEo8MzE5IVzKANUPVwprfye4+dzvHBqSBw0zJ7L9AtaSEHcdD+MFLLT90NIM3R8cHCKOZWmhDwJF6UQM8UoHvEMS9VENVDI+kiNmpfbu82un/kEj/VAVzsczoMcEzeA7E1b3t0pXf/D3xsKF946PpmE+CbH27zR+9PQN5jIkm/KnVwpv/8GKVUkDMkHD/8lCocBBcG5I6fFn1qr7j+bKt39jAK5t2aK4CO0kUwNG8tby7Q/KwxMvAvHOlm+89quH0zQN6krrWiQDF/SBk/POwoKV/XMAw99DbyIiTDjboJF6msaRMVDSi0qEusKQxhHBC+NjqMgKqAY6Hr7+0cARYgB93o8hOyyYBP1IvI0z7zAGwLWTU3R2duZvIKQHHWfs28//6GPPv/b2+zTD6HoGSHkWksGEnGcsWHZhAwTnD8OeviOOCPjfC2b+w1+98D/CBLTdLPPoDSyU6wN4GHu5I0drHKDxEO0LvDknSWSX9uOmAPtMUy8/cP+RY8DiHgemo5y7ePX4X3znpU9X7CCNAV7d94DqwDNJbWltba04Mja5FZjTz61tbx4/NKkb7UPGTcUgrgeHau+5tvnmP7UHH8lJWuJZYA4rCDfxQOJPnAemsse5KJTTHHEbUwkD1DrbJTXbI6WGb4Na0yjZWGyDMk3i8uFRbXJsMKnxTn0VJLROhjTr8KXN0pHtVPL50dFxBfRj6I1JrCvuHiXY/uFkCg+e4AdMIK4otL3P+gdNdW7cLD98q7B53A6lH4yMjtigPqSsevVT3vxLn7tvWB5UdQM2oVM/kGPllO9kDzI0lvUICDQ+MoH6JJAiyJxd19a5b2vUFPp1J1ymkd5NyQ7HQPS2w1CjU86AewDTJ4e9yuMnt27+Zk0avjIxNnJNVmQ4p/RDlVsn/9Z4cOu9EyMD6N4Dgcy6xh71z4U6Q6NaCM0gOEplMjOeU+jG0hfPbwYVVx3YsKzCAhKibhhvs+zcX80XCg8/knAPo2GbtwynnKQTOpnQSu8peO6Jyvqtx4bZ7eOJREYkPrUbKC8vl+pnN7VvUlV+GtChZaZSIp7Bc13J9wMNDp+6Q6edwlkghoA5WAlOQakEE1Icx9PZbgxbHAZkJl7QND5iqrUBRKV2I3V8oGloteX1/H3ffPHUb4MOz2p129wsNwZ0I0GkXqROuO+R0UzqEtx8AwkAiAHQiK/10QKiZA+iJJYK9cSdhIuqyKRvhD1MVEZ7lO/byGiac4I1cHXG+6U8CDek/Z++/eJ/t3y7+ABClLV8aTDgsqbrWs/1uFlJjaynDfW86/lVx24wLqe+N++P32fk178wCVIpCHkHCsplUuSoYj16LX/6nxbM+4ckI/s0/OkaMi5ED4gW8PALtICHznWn3cp65vTt6uZ6Q57PN6R5J1QWiKRvguqxKsmyOzY6InuNxmckqfB3RnJJ2p6HD/oT0RVqMK8xvLKyqk3v248uLSwOKMbD+nic8B4J4FS1bgvvSdJQkGH2zXsJYN0TGiAPuzQVKoMZQDq27bqP15bOPHXAsCYVDdSVgPUQbrHSIKtFp1xo8AoQFlNV2Tsyph4cyyWU9vEjonGBMcD6WFR2GaqgMtpdAlfjNJpDe5SkC+Nd26rbm7WwhLaWlE616aw8MQESSRLFhNpsF3Dv8ECKjG1vf3C9pj3HRoevwYmZra7f+I1s/cqTYxM51Me74D4hhXKDXN+wNxqe1BhN0+zssD6cTpod64NTmBxKKquV1aduOvIboMmuuO5yMDMz4wG6+1FR3f/0yta1fzgzltXa70PX9fSAOlGoLn2Iu/a7RwYNKWibI6oQK4UKf22JP9sI5K8rzF5BhvPwww83QwuI7Dl1EKTA+Lv2V4Q1wHwcH2gcdFmcS2xjUEFJMwjpH2zT5IJISEGzwjAaMlSYVNOE02ljAE4V6rJUXt0sj3jQO/rlTTPZC7WaNoG0xlZHsomXYOPnMQrRtm0DuRvdJf+JCneefJdOm/6cjopUCSAFX4qrJiswQa3/PcJu0kDGeGNtG9QBHZiOQTRKCOmj+4aeRfZPpV6DvfwJzIVFiS6Jsw117Mtntuoa45XPTg5npJDvbCxKKsMwyUMT/tzVtbf/yWpj/2SYHP8K6LQnsZjn9va28EmbQm1gxGo08kurwVe3Kup2SNXzuUz6VjqXqAIsdcbGxtBplEmkswfszRss8DeE5GqX7vg7Qkfb9QD2qXBNoAaeA4iBS93XxlZu9AC9vbC9fWGD/NiVErf3ZfzZ4xP+B+Ggm918hDYPNcYVuMyTgPjk+nbhRMJafCwznkRp3239IpeWS43Xl8MflH399ZDqN3XDdA7OzR6o2hf+99EcVzrGxBA2M/Q1o7VaROMyDOkPPZXEkpq33Nvk6mql+NIC/fcBNa9QRa8kDDWx5PDjh+ulLxzdl5zuZg4o+8fSdJrkt49a9lTatqxj4ebFD4wN6ArmFLRfi/3Pr1ecF67a3yv6yRf0RGptMDQmbluVzz463vj4cDtTRv1dVsjsoHTg5mL+SYukX5Yc+zZWpIb9Xwv1oe/eKmbeNZC0PpZEl3TzQYg4sqkE2Ve//ZtDhm5SxWwVXEGkUm845IfX6m+uW6m/NFSGDEcENsURwYCOVN9uGLSJCNvXkjYFuivcO4HHgPnHjEEB/UNvVWDpwxhYGIRAGGEUDIR2YN8AGKehcazbj4+w3NCV0uhg8tZ62btPEbiT9cQGiHgIv1E9PDPwNGwq6taAQgO0d2DehoIb3LeK5d1Gqe0SVRhFSuI0AleEWGNsBqAfzwfm2Kucin97vitNDA9cN9SVj0dMqQtZCKMTJZ5jkck0PTWaTTwNqOoqMFxRsvzgwYMBUN/LVXncO7W5bj0aVj4/PZLW4uCbGOJTODQP7MsOZLdW/+BCqZHz1THgj9LrwAzY0tIS2bdvn2AOgEpOKsnB02OJXDA7O6uk0tkxYD7vBlY3yZzaPu7X59zl63NZ//Z7BkZMinC0Xd1hTR29YYfOzNh0AMhODjxX7UpE6zg4p29ubb14g/87ppjfzGbMlW197L4r1e1aSq/9lpCMvNMugSjbA/3M9WwHJPY0Ky0/NmGwlIhgbDsPMkD65ULVe/6q+7VyYPx7XSUXBnJGHSMTdVX6H2iZq7wrKAx/dQIUVoFNpagcfkipqoWhzLkk1B3ehsgsD3ggNy8AEf2F1vS50PTET9ZYkoyU1v7R1HBabR8/2rNThkJVZs3U6tb+emnj6CCpzmpaKrKvtDxqVCCoF682XlquGX+UNOmrCgVOqJmGkxkvLJSuzOVSwWHY2NZZRMNuLqWTJG0czVvqFEiF2xsbGyI8G4j9jKNPf+NG/vJDj+wLJwmV2vaNkkMTmQSqKGHIOgzup26Ul69tyX9JJO95B+AUrgfWW8UmbFWgx/mercNpo6wnRTtipDYwWtgvV4OhClVCMIYg0DndzfQjjgrCtlDAbteT0SYBSrqG4ae8ixaxUgwwEnd8KHt7dWv9PkXROzZU1JYDmjSpf/vwZPrbqYT25YZl3ZyZncUwWFBT6gb2Tbr63okr8FA63NE4hboVleT+Id4Sxs8EDvbv4XstdE11Xd/gTatydwPJTw/O7lvXVMkHSL3jChTcFyV+AANz/MmMdHJuPPenwAWeCbwwiN/XIGI/JCkgzP9xRRq2T+VJrWGXfu/gZCYpicI2O5oubvb0SEahtPTbb+YD3yI5FwTuaQzdRcmCGXPospqc3jcCj38kcGrH/Ora0cbtzf0aaxxISk4mqYSZtCHTZM4U4U1hF95nINIdjAXxwzpa7NGmI1zEMu9RJRSApFsA8d9Y8l9wmP5VYEsXZZAqI4O5GuGJs4Xa+c+kkyCn2gkGDyycctv1naGJIQbnYZY0No4kB1RhJ2mH+SBkyBvz9QtbjvJNXWWvo0EbrfWgf6TrW2spIeXaxiRCnEXVsVBIONosfQbP1FQeqmjgjm0HAoxQwQQxcDfAIEoUUihNUwl9SZLG39isbSyP5YKDpN2WQaMgM9jYLNw15ZbX9ydUroVda4Myb36zXl6t0pdhxq+EAfVVNUMShu4kUqlL9WDwct3aOpxKJtpyN6J3f2RUf9S161PA+N+MKzPB942AyC8ue8MPjxS3/mBiOK20nw3WhWhRRl1fKzs/XvS/FRD9aZmF9aCZS4Nu+NhGRSSuhL6rU8r7qhLIrOAsoHch4BLHgpE+sQnXqw1rV+iOK0k5czwMNYxiBZRKtaZjmjaVe2E3j0JaqwOZ5JLvWvDgyJ2C+ilsWS2h8OJgVrk0lsv8UFXkZ+BgXMXDjtZTy7Io1oaAg6ySPu5KzI0Y1v0zwOHfhMkk+qsJNEil0965+a0v+lxK9wIMYTRB9IPeSmEsQ7tGrWGp/co34Fe27YRwUFYNRSptNxqjSjNugfLQ0yReBZ11aXzEOJVJ6t8H7vv9ECQZ+qjxZS64Zuvr69FLVigNKfffqPCMfarAylW39KVjs9kxSVJ2JC4mgcGSjQ+l5Qed8hdeXfc3GqG2Acd2FdcJs2FDTj/gbK9/Rirf/ECaVWYnTTaQSKsEkBpAS7PlNuNNY2G3cRT7B8bgosQFRo/EqYaBpwMvFgbRDrUD/lsuWqD3k1OggV9GiYzSyDA0L/BIw3WIAwSjdqsqrh8yyw2sYV0HUeRMml51kMpah8EPmU6+YvHFbXYaUMRpx/bI5NycYHyw5gbzrCwwIal9TELai1IBXOwh6t8iN4ZwM8VDnYt4kbZriUhTZXgtrgsKFYx9SQDyApRWsLlWxgAoDLRrcwYI5ganxAhcLxd43qCaoAK+t/oWLmlGNqpBPuTSdSA6URQZ10ZEphKpBmpevuGGJJnoXFMUV6pEDM9zk2iUxCxxVBfHxYuP2C2WmX7+ZqXyvsG095gsxtUnYlagFYf8ZN59ySXGN3WZLsRu8jg6VtikxMVMMAZYmh61UmqiGKAGDHBqMgboBDiK0bAcTdoluYIK5IFggQvjIzBqtVZvmLDWkUBuv4cJV2BYLG5XzETyh++azWICUFRnA1RaYATLpq7MA/Feh8FdgYVu4G24mMjFga1rlVo9AdBX7n0nFo08BJpSGMqYfwL3Lve4pOJitAcPfvbthVckJIqeICwRx8D9MAgcLaGTyPrvwhrYugjD7jV4gsDzXeDqCw/uz/359nZpNC6JL0vquqHJq6BeXIX5XI3GFIi016NHjwrYj8kscU4HcnIRlRZ4F+pMb5wpZqt+WPn7x2ezs3K3EQwGsm8kpU8Xy585kzfeUBR5FXRGiVH5437+xn876Fz/lakBzQCVk8TSLjKeCieyOLgWEL14prQzL9r0hgC8xhyDui/i6JEx+CIhFwVMDDDiw5+v+nk4PEu4iUiE6HbFfYX9orEEagclwDvQ48DRQIh5K45t5VI0TOHI2h0RiFK3675TccgCjGoL1wjPgVDvGE2Evj2gwK50jwnnYnl4kEOnyRgkZAxAynrstWgxBhoVQ8XxEmFApUL3FiH98E88DmL8fEfjRSSAdhDo3/ZdF5TvQBNMhZGWu1mEpMC/bY+5MGwLGAMJ4r6jXB+MGdBESkBXcSK8F1Gc6wJKI1FODDIrRA2wNznfse9PuHwET6+wJfD+qQcAsMlAUklvk7QUm93it4JhCnYr8leCOQaBjCNqX8uYM6AaC3zTRVSF/1Y8hMCc6aAFasJD0E+nF+VjmQu4IcBDBnBDrTdsgwGbpEofVyB8KtWKlyH8ayO55PcxuCKS9gGinDoTzIC3JhH759FIB4dfL5YqSUAGMCfexwyIxB/KQMQVWN3bahs3xcVAPQ31cDjEmHSkwcr1cc0gpYccuCmfnJqO0n0BMTiOJ4KqOO8JT8RcCWWrULic0tWrci4lEiQwwAs2twHXW/A8L3b7IFN45JFHmlFtIYauwtGmjVh3R+QQFaTx5m3X+E/nSsQxtdo/e2A6M9phxANtSZUVsm9APnRqqfqgnB78DhyFB93C/O9NB9eemhpNC23ZFx6OUMxDFgU5fFICSbKUtwvrdX7hxFzql6YGE3rQJukwVgP0fnQ/O83X6umgmCZa5clbElewRVKo+hUvoHlF5q1XuaGlFg3RvClFOxED2hcYtxyQt54bwvUgxpmCBUdZh7sW+8DdIOghCjNAGAiBhTGRyFOSVztgpiVgEqy9MpGgYAdNXgF3m0VIkARMGIweMUjWYRxUJa4DOtSRkFC9E6oHnI0wDIYV5maF1A5ZRzaGAzRiu2GFeW4Nw10QXUbj5zsGVkz5l7gM+67E5dYwAQzPAOz2EA3s/UZKqFUd40fmiGsDjMHC/jC2BM8ynENQIeVfJfkLXzwwwPbTnrT6NkSL50NRyfEp9X3r12qfqbjmArDdRXwOCiPhcYxf3yAxmLRnSgbWXGCdxmWOBrcQPw567gVjACLHw2tgtCEl/SINxTZ7wElA2husGWCjOK6j8VbcG+/KmoQjBrwcJKsFBGDFUrpf2WqcNMLGOFWacEVrWFYCBo4k3Qe9iBJyGOnGI32Ptd47iEwBC1OAREgUtku6iMyU+hlTWdxHIKpbR+/P1ESVoL5BKngqmVut1hxN1xsxWujW1eL3RqKLCJlCM9nsqcCuHgXN7SQwvZvAJPIo7ODvDBkiEOKa7fNT6/bixQOO+2G1WX2npTrBJiZ0AJPcnkwkkmO+az+Rrd/48PhkQvYZaeVDCSMUwOG3FrfLF1YbZ1e3vZuelDxz39y+oSCoPcn6QHEbFAHX81GiC8RAQz+B1BV2lgcDphiSmhPWYDoNBr+PTE1FIb+OA7f4GH8u4kk6GAMT7kTP8TyM5Awc17VFyC3jqfZjgPckTck0FTbCtVQWpGbZBd1EhYUOXOvxIVI+pKpGp7+eRAEVoKYEtu17YyPTsM6S4rv1BJCRKt6R0AGVKckl5GFVCqYkNWGOj4+LNG1NJ5nQs46mVW+CyqmO8ylguuOT7YaXTyTsNdvTt2ACsK9tffPIIzCcUsYVKdyvG6YCZzBAtQAFW8iCY+lg+4CqRVmcvGP8IVmvuCVgDpucBeTBB6JSiYBS3s/L8799UCsdN4xmBGqTF+CYolijNnIDYTg6kFAODxQ/9foafwOGtgrXBPg8fA8FqufCpkO5IoOqSJqu3A6vhMiTAPTio0IQBkxquitRquE/44IW3a5BLMWGqZig+2MwA8YHyBj1yHm/6DdhY8AbgHAjazESSb+chZgpYAJUrAth5jFIPJ3vWqtAMAPQAgKGGxlne6IqglAMCR24pNpo2CaLslJ6nZ3CpYoFfAIWv+AV610Gfqj0WwMcQgAyDSE3ehkQRut93taFG4BzQeaAHBt5JOzRdM669vdTVP9syU6dDeTkDSJra0RSt6JYAjmVUpyjA5p7gAoJxrqcHcJwxuuW58iOl3VKt48eMIGIyI5BkTav+/GVzaXvniv/O5fRlwZyuVuTU5P+SC71zwkpy1E9v3bEwAnoviJpRtiNgiCphm5CuFvZTnAQmlIcHw9NCFIztCkPW+nK6DkKPUACKhM2gLDDoIUBMyFiEtcXDNgrWgGvwT1DhOxk92GpsdGsKd83bH3oZkN+HbbsDCpoAOGfJKWbnxrL8hEWw+l2pxRDK3qI8sdDSQsHXQtdy6QGl0LWORYkprGcoc/m7A/d9vWznMpvw0f1gvBDSmX+V4aHZBOhdAczAYGyUbKr+Yq3MJW1F6q+uVBqBPZAhpvdAm7fsDm4P1P9RJEYN4A/XcIcZViyE7Sy9BvjCX8GQ8eDDmMuAUTnYrzGdccNV4dyGTI2OgpnK9gHQuTXJ/3FD4+MZASx8jYGtLHdcMsN175vKpcLeWdQ2EPTydn5repnlmraNZmGp9EWhzYLFOSCKVEOjD80ce1C1uuOFmpTAKpECzEgYYWh5oeh2j+Gmot8SaBzx4PViohIUUECGBHtdjIGAZfheGPMA+OkRbg4wPZKOXFxFeRoTTtHRFwyNRzXS7M+fcebzMPAQWnP+M6bjVE3i2sGAuNSG7ZjwGGVlD5zakbnUdeN3u4sCB7mg04EQqQ+13NRB8yXuIg+xbdEi9TopsehlYEKDAMZA6pE0fcUvTPFcU1hD04ox6pW/VjZLts1l5ZsW7WogLhMG9SD0fEh00CXVgcBiBPKSaHs1LZq3tKwKnu+U8tgWH67dVy8lBgOws1N55qeHnr5oQP7Lo+OjQ/BFgwF3pZJCemy6kch2w1bIAYnCgl3UwbzzJZFv80e4fghwuq6cO/y6A3l0blhCvdtQ1I57UFPkY6OobI+qmwNy10qBNIibNusqmo7/nkSVT8+cSD1/sTtxv9U9sunnWpAE7XSe2aS1rFUIiVsHB1B3U27hy+Mp34QvaHax3DohGQSCfWSjrGIADWNPHEo9bELt+uZmlc57Va8RJJsPz6TtB8xjFRHboiI2QA14tJK7Wbddq+4tlWve4NvrVb4tX3D/vFIPd3RspOmTp48bH7qwnplxHb1t72Kqxq0cnwmUX9sKJuk7eOnzQlcXa1srRSdN0AgryHKVXUj4bnOZ9KN65+bHjWSsJ2tNZJELk9AXjhfeJVJWn5q0PuCaWits4JjSCcM8q5p6+Nrl+wzTijNw5fbiBgAHUWvcyChJiMiFKpibxwD9uX5KCjhryidA0w3DoIEbKBB0drX38YgUgpkYAxNQtTh+iQnfRCD+DcK9MCL9wYJHxOD0BAXMwbsZxS4ZDPSsd28qDuem9rpqxcxAFZAxsOicP2IweDhaBUS5VxFY6JII9+ljwjukWaIMQdmykzUQaPcD95jY4HrQZ0iLUaNzKGd2YVNS7cIP22W0mrO03YA9aLvPpsySSZFYBGYuePHx4IAutC7g67wYISOjueTtxdLN0ACXTJ0vdKwqw2/Kd3iw4/uOHze+w5lTiQL+u+bSa0u11emlm+Xrg1mQEjkKOl29+EcLYABlEpeNpsjttVIZokPzF4XwTS8o+5BiIcGax4EyBhaYfEh0yQgRrTndRMjprJB9xiAhM4s4rj2Yt6R3tyqOB+cGFZp+7X4+0DapO85qJ6o1CsnGC/BQdeIqSfbXq/WCfpEfgWIRTwHYiyYbh14oEr0Mqkoso+QoWxCfp/pv7/aKL4fv88kNaJrXcFWoo4BJfMbFf/Ccu21MAwu4LkCtHzhukWen87UH5oZz6p+m8jGsYzmksoTCf/JamPrSfwL1tEw9ISoGdEZjSuRze06eeVy8dVK3Xrl8ME5f3p6H7Fs96NK+frfmB30DyhKsjOJDcZzcbFYPbdUe3V8YjK/WLA/fv+UMtxh7IX/DoynMw/knc+dWvYvwo5j5ixDA6SIe+FUM0NfGELbEWFLDRXuykA46iLGgOmYno+GGZXswhiQG2DkkR9E4cO+phggabKRQGe9RBdGkrgdcWGxCCQeNMrgRqKBSSR3NAuAtj1MB26d4v36bkJI0KsE5GlnDPE7+6JDxNBekOiv6jRjD0LUJRhvhkPLMN4MQKkEGqH6AIYoa7NNT4ylJs4DG/7EueFGdEVfNxzKnUAYrtpjJKROC1yffHo80Ofni/XTC9UXAcCfB7lVsjk7u2F623DIB9t1XWwHxrMDw2nnS0G4RlYLVX9x1frnA0emx6Ichx0pIYzpoTA2YY2NANUiIN4k4A5dxPe3m+B4pFoA4Qn1DSmspX4FTFUDHzMTpG4bBmvaMDD5DH30wESLls1eOr/m/PJQxjghCXWkjTmIXAKJDGYTLduDCMLyA/G9JNEetzV6JKL0HeFCN2QWpMT7TvrYO9CbhWX7MOdhKJdsfd+dq6GC7lSpO+T5sxsn81X3OUWi63hGrUZto1oPv/PK9cbBXzWUzw1mIuLlbePv6DuqFdFxdBXRt02+88bqG9fW63+VTiYuHDp0CC11j/Lq2udnzMr7Usl0x5hQTShWLfLi+fyPqpb/1wMBd68W2GtTA+6no3D0ODISVRSZPDqbfuxWvvCZtSq5wUP/GgbXYSKjj4ZH5umcKB1RoREoFeoqA1UGc0/QMEawBgHoc54J3+vt+WGtD4+Nj77TzKoE7uaYDcsx0S8pAoI6PpHu26a7t9SJYrEoctDR6IaQO/4+duXhB/o3a5Zt0L5901iXR5WGx30LF2pnP8i4MsIMSklvP4J5hY4fNXwmul+T6Ivv90wWWd975oNMYHNzU9gUML4gHkvHx/cbToChIs2UsCYz23Hx8ebB3TH4RfUEGDl3Y7P29KnVpysN9xuB723V6lVWaTinzq95L29XLKI03xcY94eSK5MyyOhAitwuubdcJq3pqpxUJNon/14QFxqORJg7qCI5TSYpSaY9MRxwVDBQCZY0ypWJ54+vNWQghaS+kagYkYfMNwyqgK6QOdTrtVNvLlp/+saN4q3Qj6qAER47vaLxI7HhT0XkTVjkhbO3z21VbUdpL7hDo2gVtPt4zexY+Ggs8JL4nsaOcGW4b7tqez84t3Yefoq1RXSDPC5Wx2J7Ft4LqgN5/q3V62fmy18DdeuHzRIDUfQgD09fWvf++Nun80+v5CuhqD7W9jYo7BfRB35EpGmzX5xnZCOok798deHkT66X/h+AOn89NzcXpDO5Kbu6/VtjfO3XJoZScodLk0aq8+uX8/NX12rPwKE9D/3cXKvJLy5v2WWpq4ywQC4DSfLeueRTJPQ+DBuWRLUWjbmwewmJB4YwDLDe8+dHwWgiwEkEPWK9R4DdJkPEoOziL8UXLcAGxAcAbk9X61YGkUHo+z1p0UB0amSV7YQrKNERORw5cqQl4bvLV4GEyNTrdgZLnYV+byg06OwE/kK9ZknHOMIxKo8d1aHkupYCaDaKgUuy7/fmMjDBRFQ4UvhGXuzWqNQaWTj3qsT9Ps8MtZCGMqNhz3hRpUCVKH7xaI8tg1PHgalioVKlGf4s0lplqSe2AiUFGh/zJct/a37r5quXi89V7PCrmiK9ySODKapAly/lG3/u2/XMh48OfxhUBVlp9hc7smqWQ66v127KyqCvSuEYgD1iOV6L8UjNMNqm8TXEylUaDYcVPRhwHL/TUAb9giJPAxicQAysvb4mMynzB/A7y/Y6EINIheehjqXWQtCDkIEODg6Wapb/9WfPWyRfsX/v8cODjw6CCqFgVWgpQlM4f5Bc5OJaqQFS8jnJSM0fPxAetxy3pVPHeRtwRjBfR2oiuFSChKOIhNrniuN3HE97c7764mrJv/7xR4Y/NTaQAPCgdDwT+1vON/wfvL3+1pu3Kl+FR32dc9+KhY2otgSPoZy9fGHVtdaKq0uPzqY+8eB05tBwNqFiMh6qCTuvjYuyTJEeilWbX14urZy8Xnwd1ICn4Y/fnZ2dqYMkT9n18t8c9Bd/Z2xYytogQdqRDo59caMSvHB+4xno6zl058J3AJfpmWt578zkgPURRVY7jabw/Lmx5Ojxqfrv/viGs7C6tvbc6NgY7IesETVUorICXZ4NEX0a8objOyF6DOALTG/GgibCPSjtlnSEqgTKDTjcuMFAhGWvVng1GXg3aNCbkC9TXrBtgKl9QpHREIJ9tBvu2is5gRSuevWtV5Oed430j07G0sGLLotKZMXuSiTQuC5eoeDY5fza6VRQz0usb4UJLEJ90w18GxnV8vKyv7a6vmCGpW8B15F6w6oAWbGg1G9x8NCg9Rct9WGfMG3Yhsp8deuVW/NBZTRnjKDDJp1QB7KmNtSuyaMOX6y562tFa3tlyz5frHunAUK/CodtpYtPoc7+/Klblntzo3pldsR8ZDxr7E+b6oAI3fYDe7XQWL15u3rKSCtLpy7WL5x6q9FgXWNHtFiouDeQZ2wDkquWCrevNIrfwtCkdvHQZDZyoeKsAYFjYRvB3FdXV1EVsa3y5oVz5xsSI52vQsedcPzQ8jy3jPQsqkQBqoKzvllv+H/xwgVr9dxC+aMHxxLHJgbNWUORk3g+SnUvP79ZW7uyWvmJx9UfTI3ID/3py/PfAhTasS+YJbZVdW8CXLaQIW+sr9lXN5dPnzpr5dsrajTNKdJm2V1fL3vPLOXr1w+OGe/dP5Q4ZGiKeClH1fLKtzZqS/Mb9XPFuv8jIL6XFUWu9C/eRFwQFa/cLnkrq0XrtVcuFx6byOnHJgfN0aGMPgXMRm9m2DIY39pGyS4uF6zrmxX3DNx9EvbzsqwoIZ7/xYVFpVLcAM5VPvsioW91WVLQzUrrrl/brrnfg/1aVtWEQKnQ95Uz+cp3Lt5Yq3ERSta9t/gKesasujOKyBvd0Vxm6D1SBFrukv+IPEAnY3XbwzdH+ZxFNgYNVAPBGGgffTxSJ0LhfsQNQEgIG/gWDZz/1ZS43i+9EOAgnCG20m9hEabcvn1bWEu7CQkZAxygU1LorBpy377FJgP3LDIileLv0DV45cqVVpQXjO+a79h/mFCZ3i9iC6thg/SpAZFu4JxqtXq9Ua/+mSGHz/a1scAzQaJsN21qPcwODz0ill2yNuYbDfv/WgzCYYCFg80ot1GYe6a9Tg/0gwFS6zCmTUmWbsMBy+9W9xP2HVQT9tzatn9updDA2o5YNzHd7Ac2ly/C4bhC5EZhsRKsA6TUu2Na8R0iIDlqqqpsXL9+FdWKrzVs9yVR66EnDw0PHyuCZLfiPUSICmhgbTVf/teAMrJ9zg2+wyAARrAax320EResJf/rlaJ9ainfmIHfxfibRsY8/FwBQlhIJNQiqEznVh3n+e7Cwzh+6LWGCH1hYQENbNdAHfxDYLAdc+VxHCZnxWRCLhaqwYX1bWuO861DzTVD6GnBui/AvtwAppC/mxw9UDsWPLgnX/V/uF6yx84ulPGNWfgGLKPZJ+7eBsxnHd8gJst0E1BUGJ9znGu1Wimv5bf/A6jl3+p77nbWcJnxyHvTVOPK2+XGn4JA/94u94l8JcKCTTSIo40G7jNlkxu8mdvTHb+In1AY7iKvD3olNNf1zN3qPTYRgwdA0sFnobQfHh5GVLdMKCU/TQXz9ldsdX+Pr1CAoSzztgJZ3e7T3phxqfu9l6gfNMOl6S6J2Lz9mfizjDR+t8/sM+5dyrlTBhAQg05WMd4q9jZ0V+qPq+XjpnQb2nbjOGgYCzldh704Ffcngtz4DgXCQV/mitQ3kSwMdxALjL+syrAG0p2u3ZkvCnHQ4QuqQgv91q2lZ/e1QJAwGj9Zh/0+yTlt2V/aQ9hhLWBcUplLveX5YsTZ3D+E2cuRAN19/ECcW3DJFuP0jY41o+Se3y0hDIrQH+WidN0l1nbe2t9+sFu/OG65ue67vCuhZw13qkXTnvviNYkYbEg0QNIYBdwMXU+A5NcZpz2BhhIGo/mg7QYB5rww8c4Y4D4yQEJtx9LY1yvBo0pHIeqJAhKiixH9pOhubIVd7rV7OlQ/y77oHSpQ/Je+FvT/52fyn9N57DC+KPAMEQK60VG1xrgapE/XcyXOQhNUfJ33qbURub4ZB8TAAlGdCxgDfKEBY0iwLknaAUtC0Gpdrz4xOSnKiyHHwofG1ml8uMgcvIea93ttr+21//wWJ+aJNHygT/T4IS1itDGqfOhqVTU9lHmQkinRIyN2F42HwvCN7ugwEDaFyPhowBfNgCLWl3eCGlGZGBnK3Xf40JNSlM4rMRai8e8iwSS3pgsLjYDd71jYa3ttr/1/xxQwFghrV4ikrUjyo43viBuwcT/kofDguL6UkANMEKT9qnOFUbwIRoR4YcwYgKhV3w8SXUblDsggq4k5X038s2vLhSRr6oDlrfxl27b+BaXS+fjSOKsMGcQv6qu/9tpe+y+FKWBQHeYIxe/7jMiVDiUV9nf3G7WP8GbZYdQ0EpqkJXQDS4D32BgwzwYZQgh/DJuFbLGOXiKK+CO7lHUjREkOzZT9cIa7LCoAwkLi1e0y931O2t+TAPej6w5tEO3hz9360J7Ksdf22k9px2gaMvEVBpgfhKkGnRW9qaKoburYEf3YUCaxwwSEq0bqm+GMzfUCLMKEHi2OyX2K4zgJxwsMStV3MMBwEUEWV//AuHOXsWoQhh6lvdZi9HO35z+0N7RJIKoYE5Vq9tpe22t32+KCO2hDQFtCvxdL4/tNmcwEsTJCd15S2kzT7Efjcd6NyBtqphigV8IEnWSX6sgdGkXb70KXwTdNB7T/G2paGYg9gwAVY35+XlhPxctX9lSOvbbX7tjQdhdVOHvnt8zj+5iAHRhxohljd/a3cIql4wPxFqo49whDJJOMU3zT8N35cYXxEUM9QweI2r9H1694BkZvLS8vkwceeGCPMey1vXYnwgViRdVBpE/fgV4wV0yRaMrUFIKh5ky6M2PA0GvkB2EUBBExhnK5bFgNK+SSa1N6d25YTOwIA0/Ezu/2qvo7NYxWwzx0Xdf3YiD22l67gwqBH7Qn3Kn5IdcqzFNurDHnbmlTlijfrlpYYE8Vka/IGG7cuHG2Wq3+K3xZJyHkLsQ3hnRRjGhagF+2f1qiRhtEjBr6JR/ttb2210irGhhWGb8bRA/0uH7Wtr/27Kv8x5zfZdwW1scl1AemcDFGKHRPWu+1vbbXehjS3hLstb2217rb/yvAAOPaE/C56ZAdAAAAAElFTkSuQmCC" alt="PHPSandbox" style="border: 0;"></a>
    <p align="center" style="margin-top: 2px;">
        <strong>
            <a href="../docs/" target="_blank">API Documentation</a>
        </strong>
        &nbsp;&nbsp;&mdash;&nbsp;&nbsp;
        <strong>
            <a href="javascript:;" class="help">Help</a>
        </strong>
    </p>
    <hr class="hr"/>
    <h3 style="margin: 0;">Choose Template:</h3>
    <br/>
    <select id="templates">
        <option disabled="disabled">Select a template. . .</option>
    <?php
        foreach(glob("templates/*.json") as $index => $template){
            echo '<option value="' . htmlentities($template) . '">' . ($index + 1) . ' - ' . htmlentities(substr($template, 16, strlen($template) - 21)) . '</option>';
        }
    ?>
    </select>
    <br/>
    <br/>
    <input type="button" class="load" value="Import A Template" style="width: 100%;"/>
    <hr class="hr"/>
    <input type="button" value="Save As New Template" id="save" style="width: 100%;"/>
    <div align="center" style="margin-top: 3px; margin-bottom: -2px;">
        <input type="checkbox" id="download_check" value="yes"/>
        <strong style="line-height: 19px; vertical-align: top;">Download template?</strong>
    </div>
    <hr class="hr"/>
    <h3>Sandbox Configuration:</h3>
    <div id="configuration_container">
        <div id="configuration">
            <h3>Options</h3>
            <div id="options">
                <?php
                $sandbox = new \PHPSandbox\PHPSandbox;
                foreach($sandbox as $name => $flag){
                    if(is_bool($flag)){
                        $lastname = strstr($name, '_', true);
                        $name = htmlentities($name);
                        echo '<input type="checkbox" value="true" name="' . $name . '" id="' . $name . '"' . ($flag ? ' checked="checked"' : '') . '/>';
                        echo '<label for="' . $name . '">' . htmlentities(ucwords(str_replace(array('_', 'funcs', 'vars'), array(' ', 'functions', 'variables'), $name))) . '</label><br/>';
                    }
                }
                ?>
            </div>
            <h3>Whitelists</h3>
            <div>
                <strong>Add To: </strong>
                <select id="whitelist_select" style="margin-bottom: 3px;">
                    <option value="func">Functions</option>
                    <option value="var">Variables</option>
                    <option value="global">Globals</option>
                    <option value="superglobal">Superglobals</option>
                    <option value="const">Constants</option>
                    <option value="magic_const">Magic Constants</option>
                    <option value="namespace">Namespaces</option>
                    <option value="alias">Aliases (aka Use)</option>
                    <option value="class">Classes</option>
                    <option value="interface">Interfaces</option>
                    <option value="trait">Traits</option>
                    <option value="keyword">Keywords</option>
                    <option value="operator">Operators</option>
                    <option value="primitive">Primitives</option>
                    <option value="type">Types</option>
                </select>
                <br/>
                <input type="text" id="whitelist" value="" title="Invalid name for whitelisted item!"/>
                <input type="button" value="+" id="whitelist_add"/>
                <br/>
                <strong style="font-size: 9px;">NOTE: Whitelists override blacklists.</strong>
                <hr class="hr"/>
                <div id="whitelist_func" style="display: none;">
                    <strong>Functions:</strong>
                </div>
                <div id="whitelist_var" style="display: none;">
                    <strong>Variables:</strong>
                </div>
                <div id="whitelist_global" style="display: none;">
                    <strong>Globals:</strong>
                </div>
                <div id="whitelist_superglobal" style="display: none;">
                    <strong>Superglobals:</strong>
                </div>
                <div id="whitelist_const" style="display: none;">
                    <strong>Constants:</strong>
                </div>
                <div id="whitelist_magic_const" style="display: none;">
                    <strong>Magic Constants:</strong>
                </div>
                <div id="whitelist_namespace" style="display: none;">
                    <strong>Namespaces:</strong>
                </div>
                <div id="whitelist_alias" style="display: none;">
                    <strong>Aliases (aka Use):</strong>
                </div>
                <div id="whitelist_class" style="display: none;">
                    <strong>Classes:</strong>
                </div>
                <div id="whitelist_interface" style="display: none;">
                    <strong>Interfaces:</strong>
                </div>
                <div id="whitelist_trait" style="display: none;">
                    <strong>Traits:</strong>
                </div>
                <div id="whitelist_keyword" style="display: none;">
                    <strong>Keywords:</strong>
                </div>
                <div id="whitelist_operator" style="display: none;">
                    <strong>Operators:</strong>
                </div>
                <div id="whitelist_primitive" style="display: none;">
                    <strong>Primitives:</strong>
                </div>
                <div id="whitelist_type" style="display: none;">
                    <strong>Types:</strong>
                </div>
            </div>
            <h3>Blacklists</h3>
            <div>
                <strong>Add To: </strong>
                <select id="blacklist_select" style="margin-bottom: 3px;">
                    <option value="func">Functions</option>
                    <option value="var">Variables</option>
                    <option value="global">Globals</option>
                    <option value="superglobal">Superglobals</option>
                    <option value="const">Constants</option>
                    <option value="magic_const">Magic Constants</option>
                    <option value="namespace">Namespaces</option>
                    <option value="alias">Aliases (aka Use)</option>
                    <option value="class">Classes</option>
                    <option value="interface">Interfaces</option>
                    <option value="trait">Traits</option>
                    <option value="keyword">Keywords</option>
                    <option value="operator">Operators</option>
                    <option value="primitive">Primitives</option>
                    <option value="type">Types</option>
                </select>
                <br/>
                <input type="text" id="blacklist" value="" title="Invalid name for blacklisted item!"/>
                <input type="button" value="+" id="blacklist_add"/>
                <br/>
                <strong style="font-size: 9px;">NOTE: Whitelists override blacklists.</strong>
                <hr class="hr"/>
                <div id="blacklist_func" style="display: none;">
                    <strong>Functions:</strong>
                </div>
                <div id="blacklist_var" style="display: none;">
                    <strong>Variables:</strong>
                </div>
                <div id="blacklist_global" style="display: none;">
                    <strong>Globals:</strong>
                </div>
                <div id="blacklist_superglobal" style="display: none;">
                    <strong>Superglobals:</strong>
                </div>
                <div id="blacklist_const" style="display: none;">
                    <strong>Constants:</strong>
                </div>
                <div id="blacklist_magic_const" style="display: none;">
                    <strong>Magic Constants:</strong>
                </div>
                <div id="blacklist_namespace" style="display: none;">
                    <strong>Namespaces:</strong>
                </div>
                <div id="blacklist_alias" style="display: none;">
                    <strong>Aliases (aka Use):</strong>
                </div>
                <div id="blacklist_class" style="display: none;">
                    <strong>Classes:</strong>
                </div>
                <div id="blacklist_interface" style="display: none;">
                    <strong>Interfaces:</strong>
                </div>
                <div id="blacklist_trait" style="display: none;">
                    <strong>Traits:</strong>
                </div>
                <div id="blacklist_keyword" style="display: none;">
                    <strong>Keywords:</strong>
                </div>
                <div id="blacklist_operator" style="display: none;">
                    <strong>Operators:</strong>
                </div>
                <div id="blacklist_primitive" style="display: none;">
                    <strong>Primitives:</strong>
                </div>
                <div id="blacklist_type" style="display: none;">
                    <strong>Types:</strong>
                </div>
            </div>
            <h3>Definitions</h3>
            <div>
                <select id="define_select" style="margin-bottom: 3px;">
                    <option value="func">Function</option>
                    <option value="var">Variable</option>
                    <option value="superglobal">Superglobal</option>
                    <option value="const">Constant</option>
                    <option value="magic_const">Magic Constant</option>
                    <option value="namespace">Namespace</option>
                    <option value="alias">Alias (aka Use)</option>
                    <option value="class">Class</option>
                    <option value="interface">Interface</option>
                    <option value="trait">Trait</option>
                </select>
                <input type="button" value="Define" id="define_add"/>
                <br/>
                <strong style="font-size: 9px;">NOTE: Definitions override both whitelists and blacklists, and are inherently trusted.</strong>
                <hr class="hr"/>
                <div id="define_func" style="display: none;">
                    <strong>Functions:</strong>
                </div>
                <div id="define_var" style="display: none;">
                    <strong>Variables:</strong>
                </div>
                <div id="define_superglobal" style="display: none;">
                    <strong>Superglobals:</strong>
                </div>
                <div id="define_const" style="display: none;">
                    <strong>Constants:</strong>
                </div>
                <div id="define_magic_const" style="display: none;">
                    <strong>Magic Constants:</strong>
                </div>
                <div id="define_namespace" style="display: none;">
                    <strong>Namespaces:</strong>
                </div>
                <div id="define_alias" style="display: none;">
                    <strong>Aliases (aka Use):</strong>
                </div>
                <div id="define_class" style="display: none;">
                    <strong>Classes:</strong>
                </div>
                <div id="define_interface" style="display: none;">
                    <strong>Interfaces:</strong>
                </div>
                <div id="define_trait" style="display: none;">
                    <strong>Traits:</strong>
                </div>
            </div>
        </div>
    </div>
    <br/>
    <label for="error_level">Error Level:</label>
    <select id="error_level">
        <?php
            foreach(array('E_ALL' => E_ALL, 'E_ERROR' => E_ERROR, 'E_WARNING' => E_WARNING, 'E_NONE' => 0) as $name => $level){
                echo '<option value="' . $level . '"' . (error_reporting() == $level ? ' selected="selected"' : '') . '>' . ucfirst(strtolower(substr($name, 2))) . '</option>';
            }
        ?>
    </select>
    <br/>
    <br/>
    <input type="button" value="Run Code In Sandbox" id="run" style="width: 100%;"/>
</div>
<div id="output_container">
    <h2>Output</h2>
    <div id="output" class="ui-widget ui-widget-content ui-corner-all"><pre>Hello World!</pre></div>
</div>
<div id="editor"><?php
    echo isset($data['code']) ? $data['code'] : '';
?></div>
<div id="func_editor_dialog" title="Defined Function Editor">
    <span style="float: right;">
        <label>
            <strong>Pass PHPSandbox instance to this function?</strong>
        </label>
        <input type="checkbox" id="func_editor_pass" value="1"/>
    </span>
    <strong>Function Name: </strong>
    <br/>
    <input type="text" id="func_editor_name" style="width: 100%;" value=""/>
    <br/>
    <br/>
    <strong>Enter function arguments below in <em>$argument = value</em> format, e.g. <em>$foo = 'Hello', $bar = 'World'</em></strong>
    <br/>
    <input type="text" id="func_editor_args" style="width: 100%;" value=""/>
    <pre id="func_editor_preview">function (){</pre>
    <div id="func_editor" style="width: 780px; height: 300px;"></div>
    <pre style="margin-top: 324px;">}</pre>
</div>
<div id="var_editor_dialog" title="Defined Variable Editor">
    <strong>Variable Name: </strong>
    <br/>
    <input type="text" id="var_editor_name" style="width: 100%;" value=""/>
    <br/>
    <br/>
    <strong>Variable Type: </strong>
    <br/>
    <select id="var_editor_scalar">
        <option value="string">String</option>
        <option value="bool">Boolean</option>
        <option value="int">Integer</option>
        <option value="float">Float</option>
        <option value="null">Null</option>
    </select>
    <br/>
    <br/>
    <strong>Variable Value: </strong>
    <br/>
    <input type="text" id="var_editor_value" style="width: 100%;" value=""/>
    <pre id="var_editor_preview"></pre>
</div>
<div id="superglobal_editor_dialog" title="Defined Superglobal Editor">
    <strong>Superglobal: </strong>
    <br/>
    <select id="superglobal_editor_name">
        <?php
        foreach(\PHPSandbox\PHPSandbox::$superglobals as $superglobal){
            ?><option value="<?=ltrim($superglobal, '_');?>">$<?=$superglobal?></option><?php
        }
        ?>
    </select>
    <br/>
    <br/>
    <strong>Key: </strong>
    <br/>
    <br/>
    <input type="text" id="superglobal_editor_key" style="width: 100%;" value=""/>
    <br/>
    <br/>
    <strong>Superglobal Type: </strong>
    <br/>
    <select id="superglobal_editor_scalar">
        <option value="string">String</option>
        <option value="bool">Boolean</option>
        <option value="int">Integer</option>
        <option value="float">Float</option>
        <option value="null">Null</option>
    </select>
    <br/>
    <br/>
    <strong>Superglobal Value: </strong>
    <br/>
    <input type="text" id="superglobal_editor_value" style="width: 100%;" value=""/>
    <pre id="superglobal_editor_preview"></pre>
</div>
<div id="const_editor_dialog" title="Defined Constant Editor">
    <strong>Constant Name: </strong>
    <br/>
    <input type="text" id="const_editor_name" style="width: 100%;" value=""/>
    <br/>
    <br/>
    <strong>Constant Type: </strong>
    <br/>
    <select id="const_editor_scalar">
        <option value="string">String</option>
        <option value="bool">Boolean</option>
        <option value="int">Integer</option>
        <option value="float">Float</option>
        <option value="null">Null</option>
    </select>
    <br/>
    <br/>
    <strong>Constant Value: </strong>
    <br/>
    <input type="text" id="const_editor_value" style="width: 100%;" value=""/>
    <pre id="const_editor_preview"></pre>
</div>
<div id="magic_const_editor_dialog" title="Defined Magic Constant Editor">
    <strong>Magic Constant: </strong>
    <br/>
    <select id="magic_const_editor_name">
        <?php
            foreach(\PHPSandbox\PHPSandbox::$magic_constants as $magic_constant){
                ?><option value="<?=$magic_constant?>"><?=$magic_constant?></option><?php
            }
        ?>
    </select>
    <br/>
    <br/>
    <strong>Magic Constant Type: </strong>
    <br/>
    <select id="magic_const_editor_scalar">
        <option value="string">String</option>
        <option value="bool">Boolean</option>
        <option value="int">Integer</option>
        <option value="float">Float</option>
        <option value="null">Null</option>
    </select>
    <br/>
    <br/>
    <strong>Magic Constant Value: </strong>
    <br/>
    <input type="text" id="magic_const_editor_value" style="width: 100%;" value=""/>
    <pre id="magic_const_editor_preview"></pre>
</div>
<div id="namespace_editor_dialog" title="Defined Namespace Editor">
    <strong>Namespace: </strong>
    <br/>
    <input type="text" id="namespace_editor_name" style="width: 100%;" value=""/>
    <pre id="namespace_editor_preview"></pre>
</div>
<div id="alias_editor_dialog" title="Defined Alias (aka Use) Editor">
    <strong>Use: </strong>
    <br/>
    <input type="text" id="alias_editor_name" style="width: 100%;" value=""/>
    <br/>
    <br/>
    <strong>Alias (leave blank for none): </strong>
    <br/>
    <input type="text" id="alias_editor_value" style="width: 100%;" value=""/>
    <pre id="alias_editor_preview"></pre>
</div>
<div id="class_editor_dialog" title="Defined Class Editor">
    <strong>Redefine Class: </strong>
    <br/>
    <input type="text" id="class_editor_name" style="width: 100%;" value=""/>
    <br/>
    <br/>
    <strong>To: </strong>
    <br/>
    <input type="text" id="class_editor_value" style="width: 100%;" value=""/>
    <pre id="class_editor_preview"></pre>
</div>
<div id="interface_editor_dialog" title="Defined Interface Editor">
    <strong>Redefine Interface: </strong>
    <br/>
    <input type="text" id="interface_editor_name" style="width: 100%;" value=""/>
    <br/>
    <br/>
    <strong>To: </strong>
    <br/>
    <input type="text" id="interface_editor_value" style="width: 100%;" value=""/>
    <pre id="interface_editor_preview"></pre>
</div>
<div id="trait_editor_dialog" title="Defined Trait Editor">
    <strong>Redefine Trait: </strong>
    <br/>
    <input type="text" id="trait_editor_name" style="width: 100%;" value=""/>
    <br/>
    <br/>
    <strong>To: </strong>
    <br/>
    <input type="text" id="trait_editor_value" style="width: 100%;" value=""/>
    <pre id="trait_editor_preview"></pre>
</div>
<div id="help_dialog" title="PHPSandbox Toolkit Help">
    <iframe src="../MANUAL.html" frameborder="0" height="600" width="100%"></iframe>
</div>
<iframe src="./?load=true" id="load" name="load"></iframe>
<iframe src="./?download=true" id="download" name="download"></iframe>
<form action="./" id="download_form" target="download" method="post">
    <input type="hidden" id="download_data" name="download" value=""/>
</form>
<script src="http://d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/github");
    editor.getSession().setMode("ace/mode/php");
    function invalid(name, type){
        if(type == 'var' || type == 'global' || type == 'const'){
            return ((/[^a-z0-9_]+/i.test(name)) || (/[^a-z_]+/i.test(name.substring(0, 1))));
        } else if(type == 'func' || type == 'namespace' || type == 'alias' || type == 'class' || type == 'interface' || type == 'trait' || type == 'type'){
            return ((/[^a-z0-9_\\]+/i.test(name)) || (/[^a-z_\\]+/i.test(name.substring(0, 1))));
        } else if(type == 'keyword' || type == 'primitive'){
            return (/[^a-z]+/.test(name));
        } else if(type == 'superglobal' || type == 'magic_const'){
            return ((/[^A-Z0-9_]+/.test(name)) || (/[^A-Z_]+/.test(name.substring(0, 1))));
        }
        return false;
    }
    function name_button(name, type, key){
        switch(type){
            case 'func':
                name += '();';
                break;
            case 'var':
                name = '$' + name;
                break;
            case 'global':
                name = 'global $' + name + ';';
                break;
            case 'superglobal':
                name = name.toUpperCase().replace('_', '');
                name = (name == 'GLOBALS' ? '$' + name : '$_' + name) + (key ? "['" + key + "']" : "");
                break;
            case 'const':
                name = name.toUpperCase();
                break;
            case 'magic_const':
                name = '__' + name.toUpperCase().replace('_', '') + '__';
                break;
            case 'alias':
                name = key ? (name + ' as ' + key) : name;
                break;
            case 'namespace':
            case 'type':
                break;
            case 'class':
            case 'interface':
            case 'trait':
                name = key ? (name + ' => ' + key) : name;
                break;
            case 'keyword':
            case 'primitive':
                name = name.toLowerCase();
                break;
        }
        return name;
    }
    function list_keyup(el, select){
        if(invalid(el.val(), $(select).val())){
            el.tooltip("enable");
            el.tooltip("open");
        } else {
            el.tooltip("close");
            el.tooltip("disable");
        }
    }
    function make_button(button_name, button_class, type, name){
        return $("<input/>").attr({"value": button_name, "type": "button", "class": button_class}).data({"type": type, "name": name});
    }
    function sync_code(from_vars){
        if(from_vars){
            switch(current_mode){
                case 'code':
                    editor.setValue(code);
                    break;
                case 'setup_code':
                    editor.setValue(setup_code);
                    break;
                case 'prepend_code':
                    editor.setValue(prepend_code);
                    break;
                case 'append_code':
                    editor.setValue(append_code);
                    break;
            }
            editor.clearSelection();
        } else {
            switch(current_mode){
                case 'code':
                    code = editor.getValue();
                    break;
                case 'setup_code':
                    setup_code = editor.getValue();
                    break;
                case 'prepend_code':
                    prepend_code = editor.getValue();
                    break;
                case 'append_code':
                    append_code = editor.getValue();
                    break;
            }
        }
    }
    function load_template(response){
        if(typeof response == "string"){
            response = JSON.parse(response);
        }
        if(!response || typeof response != "object"){
            alert("Could not load template!");
            return;
        }
        code = response.code ? response.code : "";
        setup_code = response.setup_code ? response.setup_code : "";
        prepend_code = response.prepend_code ? response.prepend_code : "";
        append_code = response.append_code ? response.append_code : "";
        sync_code(true);
        var x, type, name, list, button_name, i, button, data;
        if(response.options && response.options.length){
            for(x in response.options){
                if(x == 'error_level'){
                    $('#error_level').val(response.options[x])
                } else {
                    $("#" + x).prop('checked', response.options[x] ? true : false);
                }
            }
        }
        $('input.whitelist, input.blacklist, input.func, input.var, input.superglobal, input.const, input.magic_const, input.namespace, input.alias, input.class, input.interface, input.trait').each(function(){
            var el = $(this);
            el.parent().hide();
            el.remove();
        });
        if(response.whitelist){
            for(type in response.whitelist){
                list = $("#whitelist_" + type);
                if(response.whitelist[type]){
                    for(i = 0; i<response.whitelist[type].length; i++){
                        name = response.whitelist[type][i];
                        button_name = name_button(name, type);
                        list.append(make_button(button_name, "whitelist", type, name)).show();
                    }
                }
            }
        }
        if(response.blacklist){
            for(type in response.blacklist){
                list = $("#blacklist_" + type);
                if(response.blacklist[type]){
                    for(i = 0; i<response.blacklist[type].length; i++){
                        name = response.blacklist[type][i];
                        button_name = name_button(name, type);
                        list.append(make_button(button_name, "blacklist", type, name)).show();
                    }
                }
            }
        }
        if(response.definitions){
            for(type in response.definitions){
                list = $("#define_" + type);
                if(response.definitions[type]){
                    for(name in response.definitions[type]){
                        data = response.definitions[type][name];
                        switch(type){
                            case 'func':
                                button_name = name_button(name, type);
                                button = make_button(button_name, type, type, name);
                                button.data({"args": data.args, "code": data.code, "pass": data.pass, "fullcode": data.fullcode});
                                break;
                            case 'var':
                            case 'const':
                            case 'magic_const':
                                button_name = name_button(name, type);
                                button = make_button(button_name, type, type, name);
                                button.data({"scalar": data.scalar, "value": data.value});
                                break;
                            case 'superglobal':
                                button_name = name_button(name, type, data.key);
                                button = make_button(button_name, type, type, name);
                                button.data({"key": data.key, "scalar": data.scalar, "value": data.value});
                                break;
                            case 'namespace':
                                button_name = name_button(name, type);
                                button = make_button(button_name, type, type, name);
                                break;
                            case 'alias':
                            case 'class':
                            case 'interface':
                            case 'trait':
                                button_name = name_button(name, type, data);
                                button = make_button(button_name, type, type, name);
                                button.data({"value": data});
                                break;
                        }
                        list.append(button).show();
                    }
                }
            }
        }
    }
    $(function(){
        var wl = $("#whitelist").tooltip();
        wl.tooltip("disable");
        wl.on('keyup', function(){
            list_keyup($(this), "#whitelist_select");
        });
        var bl = $("#blacklist").tooltip();
        bl.tooltip("disable");
        bl.on('keyup', function(){
            list_keyup($(this), "#blacklist_select");
        });
        $("#configuration").accordion({heightStyle: "fill"});
        $("#templates").on('change', function(){
            var template = $(this).val();
            $.getJSON("./", {"template": template}, function(response){
                load_template(response);
            });
        });
        $("#run, #save").button().on('click', function(){
            sync_code();
            var options = {}, list = function(){
                return {
                    "func": {},
                    "var": {},
                    "global": {},
                    "superglobal": {},
                    "const": {},
                    "magic_const": {},
                    "namespace": {},
                    "alias": {},
                    "class": {},
                    "interface": {},
                    "trait": {},
                    "keyword": {},
                    "operator": {},
                    "primitive": {},
                    "type": {}
                };
            }, whitelist = list(), blacklist = list(), definitions = {
                "func": {},
                "var": {},
                "superglobal": {},
                "const": {},
                "magic_const": {},
                "namespace": {},
                "alias": {},
                "class": {},
                "interface": {},
                "trait": {}
            };
            $("#options").find("input").each(function(){
               var name = $(this).attr('name');
               options[name] = $(this).is(":checked") ? 1 : 0;
            });
            var x, i;
            for(x in whitelist){
                i = 0;
                $("#whitelist_" + x).find('input').each(function(){
                   var el = $(this), type = el.data('type');
                   whitelist[type][i] = el.data('name');
                   i++;
                });
            }
            for(x in blacklist){
                i = 0;
                $("#blacklist_" + x).find('input').each(function(){
                    var el = $(this), type = el.data('type');
                    blacklist[type][i] = el.data('name');
                    i++;
                });
            }
            for(x in definitions){
                i = 0;
                $("#define_" + x).find('input').each(function(){
                    var el = $(this), type = el.data('type');
                    switch(type){
                        case 'func':
                            definitions[type][el.data('name')] = {
                                "args" : el.data('args'),
                                "code": el.data('code'),
                                "pass": el.data('pass') ? 1 : 0,
                                "fullcode": el.data('fullcode')
                            };
                            break;
                        case 'var':
                        case 'const':
                        case 'magic_const':
                            definitions[type][el.data('name')] = {
                                "scalar": el.data('scalar'),
                                "value": el.data('value')
                            };
                            break;
                        case 'superglobal':
                            definitions[type][el.data('name')] = {
                                "key": el.data('key'),
                                "scalar": el.data('scalar'),
                                "value": el.data('value')
                            };
                            break;
                        case 'namespace':
                            definitions[type][el.data('name')] = el.data('name');
                            break;
                        case 'alias':
                        case 'class':
                        case 'interface':
                        case 'trait':
                            definitions[type][el.data('name')] = el.data('value');
                            break;
                    }
                    i++;
                });
            }
            options.error_level = $("#error_level").val();
            if($(this).attr('id') == 'save'){
                var download = $("#download_check").is(":checked");
                var name = prompt("What do you want to name this new template?" + (download ? "" : " It will be saved in toolkit/templates/"), "New Template");
                if(!name){
                    alert("Can't save a template without a name!");
                    return;
                }
                if(download){
                    var data = JSON.stringify({"code": code, "setup_code": setup_code, "prepend_code": prepend_code, "append_code": append_code, "options": options, "whitelist" : whitelist, "blacklist": blacklist, "definitions": definitions, "save": name});
                    $("#download_data").val(data);
                    $("#download_form").submit();
                    return;
                }
                $.post("./", {"code": code, "setup_code": setup_code, "prepend_code": prepend_code, "append_code": append_code, "options": options, "whitelist" : whitelist, "blacklist": blacklist, "definitions": definitions, "save": name}, function(response){
                    alert(response.message);
                    if(response.success){
                        $("#templates").append('<option value="templates/' + response.file + '">' + response.name + '</option>').val('templates/' + response.file);
                    }
                }, 'json');
            } else {
                $.post("./", {"code": code, "setup_code": setup_code, "prepend_code": prepend_code, "append_code": append_code, "options": options, "whitelist" : whitelist, "blacklist": blacklist, "definitions": definitions}, function(response){
                    $("#output").html(response);
                })
            }
        });
        $("#whitelist_add").on("click", function(){
            var el = $("#whitelist");
            var name = el.val();
            var type = $("#whitelist_select").val();
            var list = $("#whitelist_" + type);
            if(!name){
               return;
            }
            if(invalid(name, type)){
                el.tooltip("enable");
                el.tooltip("open");
                return;
            }
            var button_name = name_button(name, type);
            if(list.find('input[value="' + button_name + '"]').length){
                return;
            }
            list.append(make_button(button_name, "whitelist", type, name)).show();
            el.val('');
        });
        $("#blacklist_add").on("click", function(){
            var el = $("#blacklist");
            var name = el.val();
            var type = $("#blacklist_select").val();
            var list = $("#blacklist_" + type);
            if(!name){
                return;
            }
            if(invalid(name, type)){
                el.tooltip("enable");
                el.tooltip("open");
                return;
            }
            var button_name = name_button(name, type);
            if(list.find('input[value="' + button_name + '"]').length){
                return;
            }
            list.append(make_button(button_name, "blacklist", type, name)).show();
            el.val('');
        });
        $(document).on('click', 'input.whitelist, input.blacklist', function(){
            var el = $(this);
            if(el.parent().children('input').length < 2){
                el.parent().hide();
            }
            el.remove();
        });
        $("#mode").on('change', function(){
            sync_code();
            switch($(this).val()){
                case 'code':
                    editor.setValue(code);
                    current_mode = 'code';
                    break;
                case 'setup_code':
                    editor.setValue(setup_code);
                    current_mode = 'setup_code';
                    break;
                case 'prepend_code':
                    editor.setValue(prepend_code);
                    current_mode = 'prepend_code';
                    break;
                case 'append_code':
                    editor.setValue(append_code);
                    current_mode = 'append_code';
                    break;
            }
            editor.clearSelection();
        });
    });
    $("#define_add").on('click', function(){
        launch_editor($("#define_select").val());
    });
    $(document).on('click', "input.func, input.var, input.superglobal, input.const, input.magic_const, input.namespace, input.alias, input.class, input.interface, input.trait", function(){
        launch_editor($(this).attr('class'), $(this));
    });
    $("#func_editor_name, #func_editor_args").on('keyup', function(){
        if($(this).attr('id') == 'func_editor_name'){
            $(this).val($(this).val().replace(/[^a-z0-9_\\]+/i, '_'));
        }
        var name = $("#func_editor_name").val(), args = $("#func_editor_args").val();
        $("#func_editor_preview").html('function ' + name + '(' + args + '){');
    });
    function var_preview(name, scalar, value){
        switch(scalar){
            case 'int':
                value = value.replace(/[^0-9]+/i, '') || 0;
                break;
            case 'float':
                value = value.replace(/[^0-9.]+/i, '') || 0;
                break;
            case 'bool':
                value = (value.toLowerCase() == 'true' || parseInt(value)) ? 'true' : 'false';
                break;
            case 'null':
                value = 'null';
                break;
        }
        $("#var_editor_value").val(value);
        $("#var_editor_preview").html(name ? ('$' + name + ' = ' + (scalar == 'string' ? "'" + value + "'" : value) + ';') : '');
    }
    $("#var_editor_name").on('keyup', function(){
        $(this).val($(this).val().replace(/[^a-z0-9_]+/i, '_'));
        var_preview($("#var_editor_name").val(), $("#var_editor_scalar").val(), $("#var_editor_value").val());
    });
    $("#var_editor_scalar").on('change', function(){
        var scalar = $("#var_editor_scalar").val(), v = $("#var_editor_value"), value = v.val();
        if(scalar == 'bool'){
            v.replaceWith('<select id="var_editor_value"><option value="true">true</option><option value="false">false</option></select>');
        } else if(scalar == 'null'){
            v.replaceWith('<input type="text" id="var_editor_value" style="width: 100%;" value="" disabled="disabled"/>');
        } else {
            v.replaceWith('<input type="text" id="var_editor_value" style="width: 100%;" value=""/>');
        }
        var_preview($("#var_editor_name").val(), scalar, (scalar == 'string' ? '' : value));
    });
    $(document).on('keyup change', "#var_editor_value", function(){
        var_preview($("#var_editor_name").val(), $("#var_editor_scalar").val(), $("#var_editor_value").val());
    });
    function superglobal_preview(name, key, scalar, value){
        switch(scalar){
            case 'int':
                value = value.replace(/[^0-9]+/i, '') || 0;
                break;
            case 'float':
                value = value.replace(/[^0-9.]+/i, '') || 0;
                break;
            case 'bool':
                value = (value.toLowerCase() == 'true' || parseInt(value)) ? 'true' : 'false';
                break;
            case 'null':
                value = 'null';
                break;
        }
        $("#superglobal_editor_value").val(value);
        $("#superglobal_editor_preview").html(name ? ('$' + name + "['" + key + "'] = " + (scalar == 'string' ? "'" + value + "'" : value) + ';') : '');
    }
    $(document).on('keyup change', "#superglobal_editor_name, #superglobal_editor_key, #superglobal_editor_value", function(){
       superglobal_preview($("#superglobal_editor_name").val(), $("#superglobal_editor_key").val(), $("#superglobal_editor_scalar").val(), $("#superglobal_editor_value").val());
    });
    $("#superglobal_editor_scalar").on('change', function(){
        var scalar = $("#superglobal_editor_scalar").val(), v = $("#superglobal_editor_value"), value = v.val();
        if(scalar == 'bool'){
            v.replaceWith('<select id="superglobal_editor_value"><option value="true">true</option><option value="false">false</option></select>');
        } else if(scalar == 'null'){
            v.replaceWith('<input type="text" id="superglobal_editor_value" style="width: 100%;" value="" disabled="disabled"/>');
        } else {
            v.replaceWith('<input type="text" id="superglobal_editor_value" style="width: 100%;" value=""/>');
        }
        superglobal_preview($("#superglobal_editor_name").val(), $("#superglobal_editor_key").val(), scalar, (scalar == 'string' ? '' : value));
    });
    function const_preview(name, scalar, value){
        switch(scalar){
            case 'int':
                value = value.replace(/[^0-9]+/i, '') || 0;
                break;
            case 'float':
                value = value.replace(/[^0-9.]+/i, '') || 0;
                break;
            case 'bool':
                value = (value.toLowerCase() == 'true' || parseInt(value)) ? 'true' : 'false';
                break;
            case 'null':
                value = 'null';
                break;
        }
        $("#const_editor_value").val(value);
        $("#const_editor_preview").html(name ? (name.toUpperCase() + ' = ' + (scalar == 'string' ? "'" + value + "'" : value) + ';') : '');
    }
    $("#const_editor_name").on('keyup', function(){
        $(this).val($(this).val().replace(/[^a-z0-9_]+/i, '_').toUpperCase());
        const_preview($("#const_editor_name").val(), $("#const_editor_scalar").val(), $("#const_editor_value").val());
    });
    $("#const_editor_scalar").on('change', function(){
        var scalar = $("#const_editor_scalar").val(), v = $("#const_editor_value"), value = v.val();
        if(scalar == 'bool'){
            v.replaceWith('<select id="const_editor_value"><option value="true">true</option><option value="false">false</option></select>');
        } else if(scalar == 'null'){
            v.replaceWith('<input type="text" id="const_editor_value" style="width: 100%;" value="" disabled="disabled"/>');
        } else {
            v.replaceWith('<input type="text" id="const_editor_value" style="width: 100%;" value=""/>');
        }
        const_preview($("#const_editor_name").val(), scalar, (scalar == 'string' ? '' : value));
    });
    $(document).on('keyup change', "#const_editor_value", function(){
        const_preview($("#const_editor_name").val(), $("#const_editor_scalar").val(), $("#const_editor_value").val());
    });
    function magic_const_preview(name, scalar, value){
        switch(scalar){
            case 'int':
                value = value.replace(/[^0-9]+/i, '') || 0;
                break;
            case 'float':
                value = value.replace(/[^0-9.]+/i, '') || 0;
                break;
            case 'bool':
                value = (value.toLowerCase() == 'true' || parseInt(value)) ? 'true' : 'false';
                break;
            case 'null':
                value = 'null';
                break;
        }
        $("#magic_const_editor_value").val(value);
        $("#magic_const_editor_preview").html(name ? (name.toUpperCase() + ' = ' + (scalar == 'string' ? "'" + value + "'" : value) + ';') : '');
    }
    $("#magic_const_editor_name").on('change', function(){
        magic_const_preview($("#magic_const_editor_name").val(), $("#magic_const_editor_scalar").val(), $("#magic_const_editor_value").val());
    });
    $("#magic_const_editor_scalar").on('change', function(){
        var scalar = $("#magic_const_editor_scalar").val(), v = $("#magic_const_editor_value"), value = v.val();
        if(scalar == 'bool'){
            v.replaceWith('<select id="magic_const_editor_value"><option value="true">true</option><option value="false">false</option></select>');
        } else if(scalar == 'null'){
            v.replaceWith('<input type="text" id="magic_const_editor_value" style="width: 100%;" value="" disabled="disabled"/>');
        } else {
            v.replaceWith('<input type="text" id="magic_const_editor_value" style="width: 100%;" value=""/>');
        }
        magic_const_preview($("#magic_const_editor_name").val(), scalar, (scalar == 'string' ? '' : value));
    });
    $(document).on('keyup change', "#magic_const_editor_value", function(){
        magic_const_preview($("#magic_const_editor_name").val(), $("#magic_const_editor_scalar").val(), $("#magic_const_editor_value").val());
    });
    function namespace_preview(name){
        $("#namespace_editor_preview").html(name ? ('namespace ' + name + ';') : '');
    }
    $("#namespace_editor_name").on('keyup', function(){
        $(this).val($(this).val().replace(/[^a-z0-9_\\]+/i, '_'));
        namespace_preview($("#namespace_editor_name").val());
    });
    function alias_preview(name, value){
        $("#alias_editor_preview").html(name ? ('use ' + name + (value ? ' as ' + value : '') + ';') : '');
    }
    $("#alias_editor_name, #alias_editor_value").on('keyup', function(){
        $(this).val($(this).val().replace(/[^a-z0-9_\\]+/i, '_'));
        alias_preview($("#alias_editor_name").val(), $("#alias_editor_value").val());
    });
    function class_preview(name, value){
        $("#class_editor_preview").html(name ? ('new ' + name + (value ? ' => new ' + value : '')) : '');
    }
    $("#class_editor_name, #class_editor_value").on('keyup', function(){
        $(this).val($(this).val().replace(/[^a-z0-9_\\]+/i, '_'));
        class_preview($("#class_editor_name").val(), $("#class_editor_value").val());
    });
    function interface_preview(name, value){
        $("#interface_editor_preview").html(name ? (name + (value ? ' => ' + value : '')) : '');
    }
    $("#interface_editor_name, #interface_editor_value").on('keyup', function(){
        $(this).val($(this).val().replace(/[^a-z0-9_\\]+/i, '_'));
        interface_preview($("#interface_editor_name").val(), $("#interface_editor_value").val());
    });
    function trait_preview(name, value){
        $("#trait_editor_preview").html(name ? (name + (value ? ' => ' + value : '')) : '');
    }
    $("#trait_editor_name, #trait_editor_value").on('keyup', function(){
        $(this).val($(this).val().replace(/[^a-z0-9_\\]+/i, '_'));
        trait_preview($("#trait_editor_name").val(), $("#trait_editor_value").val());
    });
    var func_editor;
    function launch_editor(type, el){
        var dialog = $("#" + type + "_editor_dialog"), buttons, delete_func = function(){
            if(el.parent().children('input').length < 2){
                el.parent().hide();
            }
            el.remove();
            $(this).dialog("close");
        };

        switch(type){
            case 'func':
                    buttons = {
                        "Save": function(){
                            var name = $("#func_editor_name").val(),
                                args = $("#func_editor_args").val(),
                                pass = $("#func_editor_pass").is(":checked") ? 1 : 0,
                                code = func_editor.getValue(),
                                button_name = name_button(name, "func"),
                                button = make_button(button_name, "func", "func", name),
                                list = $("#define_func");
                                button.data({"args": args, "code": code, "pass": pass, "fullcode": 'function(' + args + '){' + code + '}'});
                            if(el){
                                el.replaceWith(button);
                            } else {
                                list.append(button).show();
                            }
                            $(this).dialog("close");
                        }
                    };
                    if(el){
                        buttons["Delete"] = delete_func;
                    }
                dialog.dialog({
                    position: "center",
                    height: 580,
                    width: 800,
                    buttons: buttons,
                    close: function(){
                        $("#func_editor_name, #func_editor_args").val('');
                        $("#func_editor_pass").prop("checked", false);
                        $("#func_editor_preview").html('function (){');
                        $("#func_editor_dialog").hide().dialog("destroy");
                    }}).show();
                func_editor = ace.edit("func_editor");
                func_editor.setTheme("ace/theme/github");
                func_editor.getSession().setMode("ace/mode/php");
                func_editor.setValue('');
                func_editor.clearSelection();
                if(el){
                    var name = el.data('name'), args = el.data('args'), pass = el.data('pass'), code = el.data('code');
                    $("#func_editor_name").val(name);
                    $("#func_editor_args").val(args);
                    $("#func_editor_pass").prop("checked", pass ? true : false);
                    $("#func_editor_preview").html('function ' + name + '(' + args + '){');
                    func_editor.setValue(code);
                    func_editor.clearSelection();
                }
                break;

            case 'var':
                buttons = {
                    "Save": function(){
                        var name = $("#var_editor_name").val(),
                                scalar = $("#var_editor_scalar").val(),
                                value = $("#var_editor_value").val(),
                                button_name = name_button(name, "var"),
                                button = make_button(button_name, "var", "var", name),
                                list = $("#define_var");
                        switch(scalar){
                            case 'int':
                                value = parseInt(value) || 0;
                                break;
                            case 'float':
                                value = parseFloat(value) || 0;
                                break;
                            case 'bool':
                                value = (value.toLowerCase() == 'true' || parseInt(value)) ? true : false;
                                break;
                            case 'null':
                                value = null;
                                break;
                        }
                        button.data({"scalar": scalar, "value": value});
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 300,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#var_editor_name, #var_editor_value").val('');
                        $("#var_editor_scalar").val('string');
                        $("#var_editor_preview").html('');
                        $("#var_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    var value = el.data('value');
                    if(value !== null){
                        value = value.toString();
                    }
                    $("#var_editor_name").val(el.data('name'));
                    $("#var_editor_scalar").val(el.data('scalar'));
                    $("#var_editor_value").val(value);
                    var_preview(el.data('name'), el.data('scalar'), value);
                }
                break;

            case 'superglobal':
                buttons = {
                    "Save": function(){
                        var name = $("#superglobal_editor_name").val(),
                                key = $("#superglobal_editor_key").val(),
                                scalar = $("#superglobal_editor_scalar").val(),
                                value = $("#superglobal_editor_value").val(),
                                button_name = name_button(name, "superglobal", key),
                                button = make_button(button_name, "superglobal", "superglobal", name),
                                list = $("#define_superglobal");
                        switch(scalar){
                            case 'int':
                                value = parseInt(value) || 0;
                                break;
                            case 'float':
                                value = parseFloat(value) || 0;
                                break;
                            case 'bool':
                                value = (value.toLowerCase() == 'true' || parseInt(value)) ? true : false;
                                break;
                            case 'null':
                                value = null;
                                break;
                        }
                        button.data({"key": key, "scalar": scalar, "value": value});
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 360,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#superglobal_editor_name").val('_GET');
                        $("#superglobal_editor_key, #superglobal_editor_value").val('');
                        $("#superglobal_editor_scalar").val('string');
                        $("#superglobal_editor_preview").html('');
                        $("#superglobal_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    var value = el.data('value');
                    if(value !== null){
                        value = value.toString();
                    }
                    $("#superglobal_editor_name").val(el.data('name'));
                    $("#superglobal_editor_key").val(el.data('key'));
                    $("#superglobal_editor_scalar").val(el.data('scalar'));
                    $("#superglobal_editor_value").val(value);
                    superglobal_preview(el.data('name'), el.data('key'), el.data('scalar'), value);
                }
                break;

            case 'const':
                buttons = {
                    "Save": function(){
                        var name = $("#const_editor_name").val(),
                                scalar = $("#const_editor_scalar").val(),
                                value = $("#const_editor_value").val(),
                                button_name = name_button(name, "const"),
                                button = make_button(button_name, "const", "const", name),
                                list = $("#define_const");
                        switch(scalar){
                            case 'int':
                                value = parseInt(value) || 0;
                                break;
                            case 'float':
                                value = parseFloat(value) || 0;
                                break;
                            case 'bool':
                                value = (value.toLowerCase() == 'true' || parseInt(value)) ? true : false;
                                break;
                            case 'null':
                                value = null;
                                break;
                        }
                        button.data({"scalar": scalar, "value": value});
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 300,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#const_editor_name, #const_editor_value").val('');
                        $("#const_editor_scalar").val('string');
                        $("#const_editor_preview").html('');
                        $("#const_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    var value = el.data('value');
                    if(value !== null){
                        value = value.toString();
                    }
                    $("#const_editor_name").val(el.data('name'));
                    $("#const_editor_scalar").val(el.data('scalar'));
                    $("#const_editor_value").val(value);
                    const_preview(el.data('name'), el.data('scalar'), value);
                }
                break;

            case 'magic_const':
                buttons = {
                    "Save": function(){
                        var name = $("#magic_const_editor_name").val(),
                                scalar = $("#magic_const_editor_scalar").val(),
                                value = $("#magic_const_editor_value").val(),
                                button_name = name_button(name, "magic_const"),
                                button = make_button(button_name, "magic_const", "magic_const", name),
                                list = $("#define_magic_const");
                        switch(scalar){
                            case 'int':
                                value = parseInt(value) || 0;
                                break;
                            case 'float':
                                value = parseFloat(value) || 0;
                                break;
                            case 'bool':
                                value = (value.toLowerCase() == 'true' || parseInt(value)) ? true : false;
                                break;
                            case 'null':
                                value = null;
                                break;
                        }
                        button.data({"scalar": scalar, "value": value});
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 300,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#magic_const_editor_name, #magic_const_editor_value").val('');
                        $("#magic_const_editor_scalar").val('string');
                        $("#magic_const_editor_preview").html('');
                        $("#magic_const_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    var value = el.data('value');
                    if(value !== null){
                        value = value.toString();
                    }
                    $("#magic_const_editor_name").val(el.data('name'));
                    $("#magic_const_editor_scalar").val(el.data('scalar'));
                    $("#magic_const_editor_value").val(value);
                    magic_const_preview(el.data('name'), el.data('scalar'), value);
                }
                break;

            case 'namespace':
                buttons = {
                    "Save": function(){
                        var name = $("#namespace_editor_name").val(),
                                button_name = name_button(name, "namespace"),
                                button = make_button(button_name, "namespace", "namespace", name),
                                list = $("#define_namespace");
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 300,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#namespace_editor_name").val('');
                        $("#namespace_editor_preview").html('');
                        $("#namespace_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    $("#namespace_editor_name").val(el.data('name'));
                    namespace_preview(el.data('name'));
                }
                break;

            case 'alias':
                buttons = {
                    "Save": function(){
                        var name = $("#alias_editor_name").val(),
                                value = $("#alias_editor_value").val(),
                                button_name = name_button(name, "alias", value),
                                button = make_button(button_name, "alias", "alias", name),
                                list = $("#define_alias");
                        button.data({"value": value});
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 300,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#alias_editor_name, #alias_editor_value").val('');
                        $("#alias_editor_preview").html('');
                        $("#alias_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    var value = el.data('value');
                    if(value !== null){
                        value = value.toString();
                    }
                    $("#alias_editor_name").val(el.data('name'));
                    $("#alias_editor_value").val(value);
                    alias_preview(el.data('name'), value);
                }
                break;

            case 'class':
                buttons = {
                    "Save": function(){
                        var name = $("#class_editor_name").val(),
                                value = $("#class_editor_value").val(),
                                button_name = name_button(name, "class", value),
                                button = make_button(button_name, "class", "class", name),
                                list = $("#define_class");
                        button.data({"value": value});
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 300,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#class_editor_name, #class_editor_value").val('');
                        $("#class_editor_preview").html('');
                        $("#class_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    var value = el.data('value');
                    if(value !== null){
                        value = value.toString();
                    }
                    $("#class_editor_name").val(el.data('name'));
                    $("#class_editor_value").val(value);
                    class_preview(el.data('name'), value);
                }
                break;

            case 'interface':
                buttons = {
                    "Save": function(){
                        var name = $("#interface_editor_name").val(),
                                value = $("#interface_editor_value").val(),
                                button_name = name_button(name, "interface", value),
                                button = make_button(button_name, "interface", "interface", name),
                                list = $("#define_interface");
                        button.data({"value": value});
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 300,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#interface_editor_name, #interface_editor_value").val('');
                        $("#interface_editor_preview").html('');
                        $("#interface_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    var value = el.data('value');
                    if(value !== null){
                        value = value.toString();
                    }
                    $("#interface_editor_name").val(el.data('name'));
                    $("#interface_editor_value").val(value);
                    interface_preview(el.data('name'), value);
                }
                break;

            case 'trait':
                buttons = {
                    "Save": function(){
                        var name = $("#trait_editor_name").val(),
                                value = $("#trait_editor_value").val(),
                                button_name = name_button(name, "trait", value),
                                button = make_button(button_name, "trait", "trait", name),
                                list = $("#define_trait");
                        button.data({"value": value});
                        if(el){
                            el.replaceWith(button);
                        } else {
                            list.append(button).show();
                        }
                        $(this).dialog("close");
                    }
                };
                if(el){
                    buttons["Delete"] = delete_func;
                }
                dialog.dialog({
                    position: "center",
                    height: 300,
                    width: 400,
                    buttons: buttons,
                    close: function(){
                        $("#trait_editor_name, #trait_editor_value").val('');
                        $("#trait_editor_preview").html('');
                        $("#trait_editor_dialog").hide().dialog("destroy");
                    }}).show();
                if(el){
                    var value = el.data('value');
                    if(value !== null){
                        value = value.toString();
                    }
                    $("#trait_editor_name").val(el.data('name'));
                    $("#trait_editor_value").val(value);
                    trait_preview(el.data('name'), value);
                }
                break;
        }
    }
    $(function(){
        $("input.load").button().on('click', function(){
            $('#load').contents().find('input[name="load"]').click();
        });
        $("a.help").on('click', function(){
            $("#help_dialog").dialog({
                position: "center",
                width: 800,
                close: function(){
                    $("#help_dialog").hide().dialog("destroy");
                }
            }).show();
        });
    });
</script>
</body>
</html>