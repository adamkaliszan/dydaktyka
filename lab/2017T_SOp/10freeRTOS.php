<?php
include_once '../../class.geshi.php';
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<meta name="GENERATOR" content="Microsoft FrontPage 4.0">
<meta name="ProgId" content="FrontPage.Editor.Document">
<title>Zajęcia laboratoryjne nr 4 z przedmiotu Systemy Operacyjne System FreeRTOS i korutyny</title>
</head>
<body>
<h3>Zakres materiału</h3>
<p>
<ul>
 <li>Tworzenie i usuwanie korutyn</li>
 <li>Programowanie w C: pętle, break, makra</li>
</ul>
</p>
<h3>FreeRTOS Api</h3>
<p>
Przed rozpoczęciem zajęć należy zapoznać się z API systemu FreeRTOS. W instrukcji zostały zamieszczony przykłady z użyciem wszystkich wymaganych makr i funkcji.
</p>
<h3>Zadanie 1 - szybki start</h3>
<p>
Pobierz projekt z repozytorium. W tym celu utwórz (najlepiej za pomocą konsoli) katalog, w którym będzie przechowywany projekt, np tmp. Następnie przejdź do tego katalogu i wpisz:
<pre>
svn co http://rtosonavr.yum.pl/software/
</pre>
Zostanie zadane pytanie o hasło i login. Na oba pytanie należy odpowiedzieć wpisując <b>student</b>.
<br>Przejdź do katalogu, w którym będziemy modyfikować oprogramowanie obsługujące miganie diodami. W tym celu wpisz:
<pre>
cd software/FreeRtos/Lab/Coroutines/
</pre>
</p>

<p>
Skompiluj projekt, a następnie wgraj go do urządzenia.
<br>W celu kompilacji wpisz:
<pre>
make
</pre>
Uwaga: jeśli pracujemy już na istniejącym repozytorium (nie pobieraliśmy go), to przed kompilacją należy wpisać <b>make clean</b>. Może się zdarzyć, że jądro systemu było kompilowane dla innych ustawień, ponieważ budowany był wcześniej inny projekt.
</p>
<p>
W celu wgrania oprogramowania wpisz:
<pre>
make program
</pre>
</p>
<h3>Zadanie 2 - miganie diodami</h3>
<p>
Otwórz projekt w środowisku Code::Blocks. W tym celu uruchom program, a następnie otwórz projekt.
<img src="codeblocks.png"></img>
</p>
<p>
Zmodyfikuj funkcje <b>vDioda</b> z pliku <b>main.c</b>. Wykorzystaj do funkcję zdefiniowaną w pliku <b>hardware.h</b>
<img src="codeblocks2.png"></img>
<?php
$source = '
#ifndef HARDWARE_H
#define HARDWARE_H

#include <avr/io.h>
#include "main.h"
#include "../../freeRtos/Lib/include/protocol1.h"

#define TxStart() (PORTD |=  0x0C)
#define TxStop()  (PORTD &=  0xF3)

extern uint8_t address;
extern char bHelloResp[];

/**
 * Herdware initialization
 */
void hardwareInit(void);

/**
 *  Switch on specyfied diode
 * @param ledNo - diode number (0-3)
 */
void ledOn(uint8_t ledNo);

/**
 *  Switch off specyfied diode
 * @param ledNo - diode number (0-3)
 */
void ledOff(uint8_t ledNo);

/**
 *  Toggle specyfied diode
 * @param ledNo - diode number (0-3)
 */
void ledToggle(uint8_t ledNo);

/**
 * Reads specyfied key state
 * @param keyNo - key number (0-3)
 * @return 0 - key pressed, > 0 key is not pressed
 */
char readKey(uint8_t keyNo);

/**
 * Switch on (enable) Led 1
 */
void led1on(void);
/**
 * Switch off (disable) Led 1
 */
void led1off(void);
/**
 * Toggle (change state) Led 1
 */
void led1toggle(void);
/**
 * Read key #1
 */
char readKey1(void);

/**
 * Switch on (enable) Led 2
 */
void led2on(void);

'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<br>Wykorzystaj informacje o indeksie korutyny, tak by dwie korutyny mogły wykonywać tą samą funkcję i obsługiwać miganie innymi diodami.
</p>
<h3>Zadanie 3</h3>
<p>
Dadaj 2 kolejne korutyny, tak by była obsługa 4 diód
</p>
<h3>Zadanie 4</h3>
<p>
Dodaj globalne (lub statyczne) tablicę 4-ro elementowe. Pierwsza tablica określa, jak długo ma być zapalona odpowiednia dioda, a druga jak długo ma być zgaszona. Następnie zmodyfikuj funkcję <b>vDioda</b> tak, by każda dioda migała z osobną częstotliwością i z innym wypełnieniem.
<p>
<h3>Zadanie 5</h3>
<p>
</p>

<h3>Literatura</h3>
<ul>
 <li>Dokumentacja systemu FreeRTOS http://www.freertos.org/</li>
</ul>

