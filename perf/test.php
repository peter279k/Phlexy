<?php

use Phlexy\Lexer;

error_reporting(E_ALL | E_STRICT);

require dirname(__FILE__) . '/../lib/Phlexy/bootstrap.php';

if (php_sapi_name() != 'cli') echo '<pre>';

$cvsRegexes = array(
    '[^",\r\n]+'                     => 0,
    '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"' => 1,
    ','                              => 2,
    '\r?\n'                          => 3,
);

$cvsData = trim(str_repeat('hallo world,foo bar,more foo,more bar,"rare , escape",some more,stuff' . "\n", 5000));

$alphabet = range('a', 'z');
$alphabetRegexes = array_combine($alphabet, $alphabet);

$allAString = str_repeat('a', 100000);
$allZString = str_repeat('z', 20000);

$randomString = '';
for ($i = 0; $i < 50000; ++$i) {
    $randomString .= $alphabet[mt_rand(0, count($alphabet) - 1)];
}

echo 'Timing lexing of CVS data:', "\n";
testPerformanceOfAllLexers($cvsRegexes, $cvsData);
echo 'Timing alphabet lexing of all "a":', "\n";
testPerformanceOfAllLexers($alphabetRegexes, $allAString);
echo 'Timing alphabet lexing of all "z":', "\n";
testPerformanceOfAllLexers($alphabetRegexes, $allZString);
echo 'Timing alphabet lexing of random string:', "\n";
testPerformanceOfAllLexers($alphabetRegexes, $randomString);

function testPerformanceOfAllLexers(array $regexToTokenMap, $string) {
    $lexerDataGenerator = new \Phlexy\LexerDataGenerator;

    list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
        = $lexerDataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

    testLexingPerformance(new Lexer\Simple($regexToTokenMap), $string);
    testLexingPerformance(new Lexer\WithCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap), $string);
    testLexingPerformance(new Lexer\WithoutCapturingGroups($compiledRegex, $offsetToTokenMap), $string);
    echo "\n";
}

function testLexingPerformance(Lexer $lexer, $string) {
    $startTime = microtime(true);
    $lexer->lex($string);
    $endTime = microtime(true);

    echo 'Took ', $endTime - $startTime, ' seconds (', get_class($lexer), ')', "\n";
}