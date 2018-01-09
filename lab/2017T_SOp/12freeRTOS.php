<?php
include_once '../../class.geshi.php';
?>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<meta name="GENERATOR" content="Microsoft FrontPage 4.0">
<meta name="ProgId" content="FrontPage.Editor.Document">
<title>Zajęcia laboratoryjne nr 2 z przedmiotu Sieciowe Systemy Wbudowane - FreeRTOS i obsługa magistrali RS485</title>
</head>
<body>
<h3>Wprowadzenie</h3>
<p>
Celem zajęć jest napisanie oprogramowania umożliwiającego obsługę klawiatury oraz magistrali szeregowej. Zadanie wydaje się być skomplikowane, jednak dzięki zastosowaniu systemu czasu rzeczywistego FreeRTOS polegać będzie na dodaniu 1 korutyny do programu napisanego na poprzednich zajęciach. Nowością będzie obsługa przerwań mikrokontrolera w języku C oraz zastosowanie dwóch nowych funkcji dostępnych w systemie FreeRTOS.
</p>
<h4>API systemu FreeRTOS</h4>
Zapoznaj się z następującymi funkcjami/makrami (API) systemu FreeRTOS. Dwie ostatnie funkcje są nowe. Służą one do obsługi przerwań.
<ul>
 <li><a href="http://www.freertos.org/crcreate.html"   >xCoRoutineCreate        </a></li>
 <li><a href="http://www.freertos.org/crschedule.html" >vCoRoutineSchedule      </a></li>
 <li><a href="http://www.freertos.org/crdelay.html"    >crDELAY                 </a></li>
 <li><a href="http://www.freertos.org/a00116.html"     >xQueueCreate            </a></li>
 <li><a href="http://www.freertos.org/crqueuesend.html">crQUEUE_SEND            </a></li>
 <li><a href="http://www.freertos.org/crqueuerec.html" >crQUEUE_RECEIVE         </a></li>
 <li><a href="http://www.freertos.org/crsendisr.html"  >crQUEUE_SEND_FROM_ISR   </a></li>
 <li><a href="http://www.freertos.org/crrecisr.html"   >crQUEUE_RECEIVE_FROM_ISR</a></li>
</ul>
</p>
<h4>Obsługa magistrali i portu szeregowego w Miktorkontrolerze Atmega</h4>
<p>
Moduły zestawu dydaktycznego komunikują się za pośrednictwem magistrali RS 485. Jest to magistrala typu half duplex składająca się z jednego toru transmisyjnego. W danej chwili tylko jeden nadajnik może być do niej podłączony. Moduł wykonawczy załącza nadajnik wystawiając stan wysoki na wyjściu 3 portu D. Odbiornik jest włączany natomiast wystawiając stan 0 na wyjściu 2 portu D. 
Do załączenia nadajnika i odłączeniu odbiornika służy makro <b>TxStart</b>, natomiast do wyłączenia nadajnika i włączenia odbiornika służy makro <b>TxStop</b>.
</p>
<p>
Mikrokontroler wysyła i odbiera dane z magistrali RS 485 za pomocą portu szeregowego. Port ten należy na samym początku odpowiednio skonfigurować, ustawiając odpowiednią szybkość transmisji (115200 b/s) liczbę bitów w znaku (8) oraz bity stopu i parzystości. Parametry te ustawiane są w funkcji <b>xSerialPortInitMinimal</b>. Funkcja ta dodatkowo tworzy nadawczą i odbiorczą kolejkę (bufor). Rozmiar bufora zostaje określony za pomocą parametru przekazanego do funkcji.
</p>
<p>
Oprócz kolejki pośredniczącej w przesyłaniu znaków przez port szeregowy, mikrokontrolery Atmega mają 3 bajtowy sprzętowy bufor. Podczas transmisji przez port szeregowy, można umieścić 2 kolejne bajty w tej kolejce. W tym celu należy zapisać wartość jaką chcemy wysłać do rejestru <b>UDR0</b>. W podobny sposób zrealizowany jest odczyt danych. Dwa odczytane znaki mieszczą się w rejestrze UDR0, a trzeci znak może być odczytywany, przed jego zakończeniem należy odczytać coś ze sprzętowego bufora odbiorczego.
</p>
<p>
Port szeregowy możemy obsługiwać odpytując go lub za pomocą przerwań. W tym celu dodano 3 przerwania:
<ol>
 <li>Miejsce w buforze nadawczym. Przerwanie to generowane jest wtedy, gdy możemy w sprzętowym buforze umieścić kolejny znak, niezależnie od tego, czy aktualnie port szeregowy wysyła kolejny znak czy też nie. Umożliwia to wysyłanie znaków w sposób ciągły. Nie ma przerw na załadowanie rejestru UDR0 kolejną wartością.</li>
 <li>Zakończono wysyłanie. Przerwanie to generowane jest po tym, jak zakończono wysyłanie znaku.</li>
 <li>Odebrano znak. Przerwanie to wysyłane jest po odebraniu przez port szeregowy kolejnego znaku.</li>
</ol>
</p>


<h3>Zadanie 1 - przypomnienie</h3>
<p>
Jeśli nie masz pobranego kodu to pobierz go:
<pre>
svn co http://akme.yum.pl/FreeRtos
</pre>
Przejdź do katalogu FreeRtos\Lab\EM_RS485. Jeśli kod był już wcześniej pobrany, to usuń całą zawartość katalogu i pobierz go ponownie spisując:
<pre>
svn update
</pre>
Otwórz projekt. Zapoznaj się z nim. Projekt składa się z 3 plików z kodem. W porównaniu do poprzedniego projektu, dodano plik serial.c. W pliku tym zawarte są funkcje odpowiedzialne za obsługę portu szeregowego oraz jego inicjację.
</p>
<p>
Zmodyfikuj otwarty projekt, tak by był zgony z architekturą przedstawioną na rysunku.
<br><img src="http://adam.kaliszan.yum.pl/lab/2011SSW/korutynySterAstabilny.png"></img><br>
Program powinien realizować funkcję takie same jak program napisany na poprzednich zajęciach. By osiągnąć cel należy zmodyfikować następujące funkcje:
<ul>
 <li>vKlawisze</li>
 <li>vDioda</li>
</ul>
</p>
<p>Poniższy kod zawiera kod wyjściowy, jaki należy zmodyfikować. Zauważmy, że w funkcji vKlawisze pominięto zagnieżdżanie pętli.
<?php
$source = '
#include "main.h"
#include "serial.h"
#include "hardware.h"

/**
 * Tablica buforów, za pomocą których komunikują się korutyny obsługujące diody
 */
xQueueHandle xDiodesOn[4];

/**
 * Deklaracje funkcji wykonywanych przez korutyny.
 */
static void vKlawisze(xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex);
static void vDioda(xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex);

void vApplicationIdleHook( void );


portSHORT main( void )
{
  /// Utworzenie kolejek
  xDiodesOn[0] = xQueueCreate( 4, 1);
  xDiodesOn[1] = xQueueCreate( 4, 1);
  xDiodesOn[2] = xQueueCreate( 4, 1);
  xDiodesOn[3] = xQueueCreate( 4, 1);

  ///Konfiguracja portów
  hardwareInit();
  ///Inicjacja portu szeregowego. Utworzenie kolejek do komunikacji z portem szeregowym
  xSerialPortInitMinimal(8);

  /// Utworzenie korutyn
  xCoRoutineCreate(vKlawisze, 0, 0);
  xCoRoutineCreate(vDioda, 0, 0);
  xCoRoutineCreate(vDioda, 0, 1);
  xCoRoutineCreate(vDioda, 0, 2);
  xCoRoutineCreate(vDioda, 0, 3);
  xCoRoutineCreate(vProtocol, 0, 0);

  /// Uruchomienie planisty. Rozpoczyna się praca systemu FreeRtos
  vTaskStartScheduler();
  return 0;
}

static void vKlawisze(xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex)
{
  /**
   * Jest tylko jedna korutyna do obsługi klawiszy.
   * Zatem nie wykorzystyjemy zmiennej uxIndex.
   * By pozbyć się ostrzeżenia po kompilacji należy rzutować tą zmienną na void.
   */
  (void) uxIndex;

  static uint8_t klawiszNr = 0;
  static int16_t result;

  crSTART( xHandle );
  for( ;; )
  {
    if (readKey(klawiszNr) == 0)
    {
                                                    /// 0 oznacza, że klawisz został wciśnięty
    }
    klawiszNr++;                                     /// Nie ma potrzeby w pętli for robić kolejnej pętli
    klawiszNr &= 0x03;                               /// Operacja %4 zrealizowana za pomoca iloczynu bitowego (klawiszNr = klawiszNr % 4)

    crDELAY( xHandle, 0);                            /// Wymuszenie przełączenia korutyny.
  }                                                  /// Makro crQUEUE_SEND z parametrem ticksToWait równym 0 nie przełącza korutyny
  crEND();
}

static void vDioda(xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex)
{
  crSTART( xHandle );
  for (;;)
  {

    crDELAY(xHandle, 0);                             /// Wymuszenie przełączenia korutyny, makro do odbioru wiadomości z czasem 0 nie przełącza korutyn
  }
  crEND();
}

void vProtocol(xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex)
{
  (void) uxIndex;

  crSTART( xHandle );
  for( ;; )
  {
    crDELAY(xHandle, 100);
  }
  crEND();
}

void vApplicationIdleHook( void )
{
  for( ;; )
  {
    vCoRoutineSchedule();
  }
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Algorytm zaimplementowany przez korutynę obsługującą diodę w funkcji <b>vDioda</b> można przedstawić za pomocą następującego schematu blokowego:
<br><img src="http://adam.kaliszan.yum.pl/lab/2011SSW/korutynaDiody.png"></img>
</p>
<h3>Zadanie 2 - obsługa magistrali RS 485</h3>
<p>
Zadanie polega na dodaniu kodu obsługującego magistralę RS 485. Format wiadomości będzie maksymalnie uproszczony. Nie będzie on obejmował adresowania. Wiadomości będą 1 bajtowe, zapisane w kodzie ASCII. Umożliwi nam to wysyłanie wiadomości za pomocą programu minicom. W zaproponowanym protokole znak 'a' zapala pierwszą diodę na 5 s, a znak 'A' ją gasi. Podobnie znak 'b' zapala drugą diodę, a znak 'B' ją gasi, itd.
</p>
<p>
Architektura oprogramowania została celowo rozdzielona na korutyny. Umożliwia ona dodanie w szybki sposób kolejnej korutyny, która będzie obsługiwała komunikację z resztą systemu (innymi urządzeniami). Na poniższym schemacie blokowym przedstawiono takie rozwiązanie.
<br><img src="http://adam.kaliszan.yum.pl/lab/2011SSW/korutynySterAstabilny2.png"></img>
</p>
<p>Zapoznaj się z zawartością pliku serial.c, w którym jest częściowo zaimplementowana obsługa portu szeregowego i magistrali RS 485.
<?php
$source = '#include <stdlib.h>
#include <avr/interrupt.h>
#include "FreeRTOS.h"
#include "queue.h"
#include "task.h"
#include "serial.h"
#include "../../freeRtos/Lib/include/protocol1.h"
#include "hardware.h"


/**
 * Konfiguracja portu szeregowego
 */
void xSerialPortInitMinimal(unsigned portBASE_TYPE uxQueueLength )
{
  portENTER_CRITICAL();
  {
    /**
     * Utworzenie buforów, które służą do przesyłania wiadomości pomiędzy korutynami a portem szeregowym.
     */
    xRxedChars = xQueueCreate( uxQueueLength, ( unsigned portBASE_TYPE ) sizeof( signed portCHAR ) );
    xCharsForTx = xQueueCreate( uxQueueLength, ( unsigned portBASE_TYPE ) sizeof( signed portCHAR ) );

    /**
     * Konfiguracja pracy portu szeregowego
     */
    UBRR0L = 3;            /// Szybkość transmisji 115 kb/s
    UBRR0H = 0;            /// wartość rejestru zależy od taktowania procesora

    UCSR0C = ( serUCSRC_SELECT | serEIGHT_DATA_BITS );    /// Długość przesyłanego słowa: 8 bitów

    /**
     * Włączenie obsługi przerwań portu szeregowego:
     * - odebrano wiadomość
     * - zakończono wysyłanie.
     * Włączenie Nadajnika i odbiornika portu szeregowego.
     * Uwaga: Włączoy nadajnik nie wystarczy do rozpoczęcia transmisji.
     * Musi on dodatkowo zostać podłączony do magistrali RS 485.
     */
    UCSR0B = ((1<<RXCIE0)|(1<<TXCIE0)|(1<<TXEN0)|(1<<RXEN0));
  }
  portEXIT_CRITICAL();
  return;
}

/**
 * Obsługa przerwania "Odebrano znak"
 *
 * To przerwanie wywoływane jest, gdy zostanie odebrany znak przez port szeregowy
 * i umieszczony jest on w sprzętowym buforze odbiorczym.
 **/
ISR(USART_RX_vect)
{
  signed portCHAR tempToRx;
  tempToRx = UDR0;     /// Odczyt odebranego bajtu ze sprzętowego bufora.

  /**
   * Umieszczenie odebranej wiadomości w buforze odbiorczym
   * Uwaga: do wysyłania i odbierania wiadomości z buforów przez funkcje obsługujące przerwania
   * służą osobne makra: crQUEUE_SEND_FROM_ISR i xQueueReceiveFromISR.
   */
  crQUEUE_SEND_FROM_ISR( xRxedChars, &tempToRx, pdFALSE );
}                    /// nadajnik zakłóca działanie magistrali RS485
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
Sprawdź poprawność ostatniej funkcji, która obsługuje przerwanie, jakie jest wywołana po odebraniu znaku przez port szeregowy. Jak się nazywa kolejka, do której przesyłane są odebrane znaki?
</p>

<h3>Zadanie 3 - wyłączenie nadawania oraz zbędnych przerwań</h3>
<p>
Zmodyfikuj zawartość pliku serial.c, tak by:
<ul>
 <li>Wyłączone zostało przerwanie "miejsce w buforze nadawczym" (wyłączone, włączanie za pomocą kodu  UCSR0B|= UDRIE0)</li>
 <li>Wyłączone zostało przerwanie "zakończono wysyłanie znaku" (włączanie za pomocą kodu UCSR0B |= TXCIE0)</li>
 <li>Port szeregowy był skonfigurowany tylko jako odbiornik (włączanie nadajnika za pomocą UCSR0B |= TXEN0)</li>
</ul>
</p>
Po takiej konfiguracji poniższe funkcje są zbędne. Można je pozostawić w pliku serial.c.
<?php
$source = '
/**
 * Obsługa przerwania "Miejsce w sprzętowym buforze nadawczym"
 *
 * To przerwanie wywoływane jest wtedy, gdy w sprzętowym buforze nadawczym
 * portu szeregowego jest miejsce na kolejny bajt.
 * Obsługa przerwania polega na pobraniu kolejnego bajtu z bufora cyklicznego
 * i umieszczeniu go w sprzętowym buforze nadawczym portu szeregowego.
 */
ISR(USART_UDRE_vect)
{
  signed portCHAR tempToSend;

  if( xQueueReceiveFromISR(xCharsForTx, &tempToSend, NULL) == pdTRUE )
  {                    /// Odczytana wiadomość zapisane jest w zmiennej tempToSend
    UDR0 = tempToSend; /// Umieszczenie bajtu z danymi w sprzętowym buforze nadawczym
  }
  else                 /// Bufor cykliczny jest pusty
  {                    /// Nie ma kolejnych danych, jakie można umieścić w SPRZĘTOWYM buforze
    vInterruptOff();   /// nadawczym portu szeregowego, zatem wyłączona została obsługa
  }                    /// przerwania pusty bufor nadawczym.
}                      /// W przeciwnym wypadku cały czas byłoby ono wykonywane


/**
 * Obsługa przerwania "wysłano bajt"
 *
 * To przerwanie wywoływane jest wtedy, gdy wszystkie dane z bufora UDR0 zostały wysłane.
 * Po wysłaniu wszystkich danych można odłączyć nadajnik od magistrali RS 485.
 */
ISR(USART_TX_vect)
{                      /// Wyłączenie nadajnika.
  TxStop();            /// Mimo braku transmisji, włączony
} 
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>

<h3>Zadanie 4 - implementacja korutyny obsługującej magistralę RS485</h3>

<p>Do komunikacji pomiędzy korutynami wysyłającymi żądanie zapalenia/zgaszenia diody służą kolejki <b>xDiodesOn</b>. Są one zadeklarowane w pliku main.c, jednak funkcja obsługująca przerwania nie odwołuje się do nich bezpośrednio. Wynika to z faktu, że protokół do komunikacji pomiędzy modułami może być skomplikowany. Pomiędzy modułami mogą być przesyłane ramki, w których zawarta jest informacja o diodzie jaką należy zapalić oraz o czasie świecenia diody. Ramki takie mogą dodatkowo zawierać bity kontrolne oraz rozpoczynać się specjalnym znakiem określającym początek ramki.
</p>
<p>Na początku uprośćmy protokół, tak by wiadomość składała się z jednego znaku zgodnie z zasadami opisanymi wcześniej. Wtedy algorytm działania korutyny obsługującej port szeregowy możemy zaimplementować w następujący sposób:
<br><img src="http://adam.kaliszan.yum.pl/lab/2011SSW/korutynyProtAscii.png"></img>
</p>

<h3>Zadanie 5 - testowanie</h3>
<p>
Przed testami odłącz główny sterownik od magistrali RS485. Główny sterownik może mieć wgrany program, który cyklicznie odpytuje moduły wykonawcze.
</p>
<p>Wgraj oprogramowanie na moduł wykonawczy, a następnie przełącz zworki tak, by do magistrali RS485 podłączyć USB komputera. Uruchom program minicom
<pre>
minicom -s
</pre>
Skonfiguruj go w następujący sposób
<pre>
    +-----------------------------------------------------------------------+
    | A - Urządzenie szeregowe          : /dev/ttyUSB0                      |
    | B - Lokalizacja pliku blokującego : /var/lock                         |
    | C - Program Callin                :                                   |
    | D - Program Callout               :                                   |
    | E - Bps/Parzystość/Bity           : 115200 8N1                        |
    | F - Sprzętowa kontrola przepływu  : Nie                               |
    | G - Programowa kontrola przepływu : Nie                               |
    |                                                                       |
    |    Które ustawienie zmienić?                                          |
    +-----------------------------------------------------------------------+
            | Ekran i klawiatura            |
            | Zapisz setup jako dfl         |
            | Zapisz setup jako..           |
            | Wyjście                       |
            | Wyjdź z Minicoma              |
            +-------------------------------+
</pre>
Następnie spróbuj wpisać literki a, A, b, B, c, C, d, D. Zobacz jak działa moduł wykonawczy.
</p>
<h3>Zadanie 6 - modyfikacja oprogramowania sterownika głównego</h3>
Wielozadaniowość w głównym sterowniku została zrealizowana za pomocą zadań (nie korutyn).
W programie zostaje utworzone jedno zadanie do obsługi interpretera poleceń (funkcja vTaskVTY). Działanie interpretera polega na:
<ul>
 <li>Sprawdzeniu, czy w buforze jest jakiś znak. Jest to operacja blokująca. Zadanie zostaje zawieszone do czasu odebrania jakiegoś znaku.</li>
 <li>Przekazaniu odebranego symbolu do interpretera poleceń.</li>
 <li>Interpretacji i wykonania określonych czynności.</li>
</ul>
</p>
Celem zadania jest dodanie nowego polecenia, które wysyła na magistralę odpowienią wiadomość (teraz jest to znak) by zapalić lub zgasić diodę.  
<h4>Zadanie 6.1</h4>
<p>Dodaj nowe polecenie do interpretera poleceń. W tym celu otwórz projekt <b>Cli</b>. Przestaw zworki na programatorze tak, by programował główny sterownik. Podłącz za pomocą drugiego kabla USB sterownik.</p>
<p>
Otwórz plik vty.c. Zapoznaj się z jego zawartością i w analogiczny sposób dodaj nowe polecenie o nazwie "hello", które wypisze powitanie.
</p>
<p>
Podpowiedź, konieczna będzie również modyfikacja plików vty_en.h i vty_pl.h.
</p>
<h4>Zadanie 6.2</h4>
<p>
Dodaj polecenie o nazwie <b>zapal</b> które zapali określoną diodę. Za poleceniem zapal należy podać numer diody. by osiągnąć zamierzony cel należy:
<ul>
 <li>Odczytać numer diody. Można to zrobić w następujący sposób:
<pre>
static cliExRes_t zapal(cmdState_t *state)
{
  uint8_t nrDiody;
  nrDiody = cmdlineGetArgInt(1, state);
</pre>
 </li>
 <li>Wysłać na magistralę RS485 odpowiednią wiadomość za pomocą funkcji: 
<pre>
void    uartRs485SendByte(uint8_t data);</li>
</pre>
</ul>
</p>
<h3>Zadanie 7 - ramki z wiadomościami</h3>
Główny sterownik komunikuje się z modułem wykonawczym za pomoca bardziej rozbudowanego protokołu. W protokole tym wprowadzona jest adresacja urządzeń. Dodatkowo wiadomości kończą się kodami CRC16, tak by sprawdzić czy nie są błędne.
Wiadomość protokołu składa się z następujących pól:
<ol>
 <li>Nagłówek: 1 bajtowe pole o wartości 0x5A</li>
 <li>Kod operacji: 1 bajtowe pole o następującym znaczeniu:
  <ul>
   <li>0x80: Ping</li>
   <li>0x81: wgraj firmware za pomocą bootloadera</li>
   <li>0x82: hello - podaj swój status</li>
   <li>0x10: opuść roletę 1</li>
   <li>0x11: opuść roletę 2</li>
   <li>0x20: podnieś roletę 1</li>
   <li>0x21: podnieś roletę 2</li>
   <li>0x30: zatrzymaj roletę 1</li>
   <li>0x31: zatrzymaj roletę 2</li>
  </ul>
 </li>
 <li>Adres: 1 bajtowe pole określające adres wywoływanego modułu wykonawczego. 0 jest adresem głównego sterownika.</li>
 <li>Długosć: 1 bajtowe pole określające długość pola danych wiadomości.</li>
 <li>Pole danych wiadomości: jego długość zależy od poprzedniego pola, a zawartość zależy od konkretnej wiadomości.</li>
 <li>Bardziej znaczący bajt kodu CRC.</li>
 <li>Mniej znaczący bajt kodu CRC.</li> 
</ol>
</p>
<p>
W katalogu FreeRtos/projektyAdam są projekty: ModWykonawczy oraz SterownikGlowny, w którym został zaimplementowany taki protokół.
Na potrzeby naszego zadania najłatwiej jest modyfikować oba projekty, tak by moduł wykonawczy działał jak sterownik 4 źródeł świateł, które zapalane są na określony czas.
</p>

<h3>Dokumentacja</h3>
<ul>
 <li><a href="http://www.freertos.org/a00019.html" target=_blank>API systemu FreeRTOS</a></li>
 <li><a href="sterownik.php">Sterownik główny</a></li>
 <li><a href="mod_wyk.php">Moduł wykonawczy</a></li>
</ul>
<h3></h3>
<pre>
static void vKlawisze(xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex)
{
  /**
   * Jest tylko jedna korutyna do obsługi klawiszy.
   * Zatem nie wykorzystyjemy zmiennej uxIndex.
   * By pozbyć się ostrzeżenia po kompilacji należy rzutować tą zmienną na void.
   */
  (void) uxIndex;

  static uint8_t klawiszNr = 0;
  static int16_t result;
  static uint8_t liczniki[4] = {0, 0, 0, 0};

  crSTART( xHandle );
  for( ;; )
  {
    crDELAY( xHandle, 1);                            /// Wymuszenie przełączenia korutyny.

    if (readKey(klawiszNr) == 0)
        liczniki[klawiszNr]++;
    else
        liczniki[klawiszNr] = 0;

    if (liczniki[klawiszNr] == 5)
    {
        uint8_t czas = 250;
        crQUEUE_SEND(xHandle, xDiodesOn[klawiszNr], &czas, 0, &result);
    }

    if (liczniki[klawiszNr] == 13)
    {
        uint8_t czas = 0;
        crQUEUE_SEND(xHandle, xDiodesOn[klawiszNr], &czas, 0, &result);
    }

    klawiszNr++;                                     /// Nie ma potrzeby w pętli for robić kolejnej pętli
    klawiszNr &= 0x03;                               /// Operacja %4 zrealizowana za pomoca iloczynu bitowego (klawiszNr = klawiszNr % 4)

  }                                                  /// Makro crQUEUE_SEND z parametrem ticksToWait równym 0 nie przełącza korutyny
  crEND();
}

static void vDioda(xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex)
{
  crSTART( xHandle );
  static uint8_t czas[4] = {250, 0, 0, 0};
  static uint16_t pxResult;
  for (;;)
  {
    if (czas[uxIndex] == 0)
        ledOff(uxIndex);
    else
        ledOn(uxIndex);

    crQUEUE_RECEIVE(xHandle, xDiodesOn[uxIndex], &czas[uxIndex], czas[uxIndex], &pxResult);

    if (pxResult != pdPASS)
        czas[uxIndex] = 0;

    crDELAY( xHandle, 0);                            /// Wymuszenie przełączenia korutyny.
  }
  crEND();
}

void vProtocol(xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex)
{
  (void) uxIndex;

  static uint8_t odebranyZnak;
  static uint16_t pxResult;
  static uint8_t nrBufora;
  static uint8_t czas;
  crSTART( xHandle );
  for( ;; )
  {
    crQUEUE_RECEIVE(xHandle, xRxedChars, &odebranyZnak, 100, &pxResult);
    if (pxResult == pdPASS)
    {
       nrBufora = 5;


      if (odebranyZnak == 'a')
      {
        czas = 250;
        nrBufora = 0;
      }
      if (odebranyZnak == 'b')
      {
        czas = 250;
        nrBufora = 1;
      }
      if (odebranyZnak == 'c')
      {
        czas = 250;
        nrBufora = 2;
      }
      if (odebranyZnak == 'd')
      {
        czas = 250;
        nrBufora = 3;
      }
      if (odebranyZnak == 'A')
      {
        czas = 0;
        nrBufora = 0;
      }
      if (odebranyZnak == 'B')
      {
        czas = 0;
        nrBufora = 1;
      }
      if (odebranyZnak == 'C')
      {
        czas = 0;
        nrBufora = 2;
      }
      if (odebranyZnak == 'D')
      {
        czas = 0;
        nrBufora = 3;
      }
      if (nrBufora != 5)
        crQUEUE_SEND(xHandle, xDiodesOn[nrBufora], &czas, 0, &pxResult);
    }
    crDELAY(xHandle, 0);
  }
  crEND();
}
</pre>
<body>


