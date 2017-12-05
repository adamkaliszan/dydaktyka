<?php
include_once '../../class.geshi.php';
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<meta name="GENERATOR" content="Microsoft FrontPage 4.0">
<meta name="ProgId" content="FrontPage.Editor.Document">
<title>Zajęcia laboratoryjne nr 5 z przedmiotu Systemy Operacyjne System FreeRtos i korutyny</title>
</head>
<body>
<h3>Zakres materiału</h3>
<p>
<ul>
 <li>Tworzenie i usuwanie korutyn</li>
  <li>Programowanie w C: pętle, break, makra</li>
  </ul>
  </p>
  <h3>FreeRtos Api</h3>
  <p>
  Przed rozpoczęciem zajęć należy zapoznać się z API systemu FreeRtos. W instrukcji zostały zamieszczony przykłady z użyciem wszystkich wymaganych makr i funkcji.
  </p>
  <h3>Zadanie 1 - przypomnienie</h3>
  <p>
  Pobierz projekt z repozytorium. W tym celu utwórz (najlepiej za pomocą konsoli) katalog, w którym będzie przechowywany projekt, np. tmp. Nastepnie przejdź do tego katalogu i wpisz:
  <pre>
  svn co http://akme.yum.pl/FreeRtos
  </pre>
  Zostanie zadane pytanie o hasło i login. Na oba pytanie należy odpowiedzieć wpisując <b>student</b>.
  <br>Przejdź do katalogu, w którym będziemy modyfikować oprogramowanie obsługujące miganie diodami. W tym celu wpisz:
  <pre>
  cd FreeRtos/FreeRtos/Lab/Coroutines/
  </pre>
  </p>
  <p>
  Dodaj 2 kolejne korutyny, tak by była obsługa 4 diód.
  Dodaj globalne (lub statyczne) tablicę 4-ro elementowe. Pierwsza tablica określa, jak długo ma być zapalona odpowiednia dioda, a druga jak długo ma być zgaszona. Następnie zmodyfikuj funkcję <b>vDioda</b> tak by każda dioda migała z osobną częstotliwością i z innym wypełnieniem.
  </p>
  <h3>Zadanie 2 - wprowadzenie</h3>
  <p>
  Rozważ algorytm programu, działa w następujący sposób.
  <ol>
   <li>W programie są 4 korutyny, które zapalają diodę na określony czas, a następnie gaszą. 4 korutyny zostały utworzone w zadaniu 1, a następnie w zadaniu 6 zostaną zmodyfikowane.</li>
    <li>Każda korutyna ma osobny bufor i oczekuje na odpowiednią wiadomość, która określa ile czasu ma być zapalona dioda. W zadaniu 3 zostanie zaprojektowany format wiadomości. Kolejki zostaną utworzone w zadaniu 4.</li>
     <li>Istnieje korutyna, która sprawdza stan klawisza i jeśli klawisz jest wciśnięty, to wysyła wiadomość do odpowiedniego bufora, by zapalić na określony czas diodę. Korutyna ta zostanie utworzona w zadaniu 5.</li>
     </p>
     <p>Architekturę systemu przedstawiono na poniższym rysunku. Korutyna sprawdzająca stan klawiszy, może wysłać wiadomość do jednego z 4 buforów, natomiast każda z korutyn obsługującch diody ma jeden taki bufor, z którego czyta wiadomości.
     <br><img src="http://adam.kaliszan.yum.pl/wyklady/LabSop/korutynyZad6.png"></img>
     </p>
     <h3>Zadanie 3 - format wiadomości</h3>
     <p>
     Zaprojektuj wiadomość, która steruje korutyną obsługującą pojedynczą diodę. Określ długość tej wiadomości.
     </p>
     <p>
     Format wiadomości uprośćmy do następującej postaci. Długość wiadomości jest równa 2 bajty. Wiadomość zawiera czas określający czas, przez jaki ma być zapalona dioda. Zero oznacza, że dioda ma zostać wyłączona.
     </p>
     <h3>Zadanie 4 - kolejki</h3>
     <p>
     Utwórz tablicę z obiektami reprezentującymi kolejki. Następnie zainicjuj każdą z kolejek.
     </p>
     <p>
     Kolejka reprezentowana jest za pomocą typu <b>QueueHandle_t</b>.
     Zatem, należy utworzyć talicę:
     <?php
     $source = '
     xQueueHandle kolejki[4];

void main()
{


';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
Kolejnym krokiem jest inicjacja kolejek. Służy do tego funkcja
<pre>
QueueHandle_t xQueueCreate(UBaseType_t uxQueueLength, UBaseType_t uxItemSize);
</pre>
Funkcja wymaga określenia rozmiaru wiadomości oraz maksymalnej liczby wiadomości, jakie mogą być przechowane w kolejce do momentu ich odebrania. Więcej o funkcji tej można przeczytać na stronie <a href="http://www.freertos.org/a00116.html">http://www.freertos.org/a00116.html</a>.
Kolejki zainicjuj w głównej funkcji main, najlepiej w pętli.
</p>
<h3>Zadanie 5 - obsługa klawiatury</h3>
<p>
Utwórz korutynę, która cyklicznie sprawdza stan klawiszy. Po wykryciu wciśnięcia klawisza przez krótki czas (mniej niż 2s), zostaje wysłana wiadomość żądająca zapalenie diody. W przypadku wciśnięcia i przytrzymanie go przez dłuższy czas zostanie wysłana wiadomość żądająca zgaszenie diody.
</p>
<h4>Usypianie korutyny</h4>
<?php
$source = '
// Co-routine to be created.
void vACoRoutine( xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex )
{
  // Te zmienna muszi być statyczna. W przeciwnym razie zostanie umieszczona na stosie.
    // Inna korutyna korzysta z tego samego stosu, przez co zmienne mogą być zmienione po powrocie do korutyny.
      // Ze zmiennych na stosie możemy korzystać pod warunkiem, że przez ten czas nie wykonamy makra,
        // które przełączy wykonywaną korutynę
	  // Czas uśpienia - 200 ms.
	    static const xTickType xDelayTime = 200 / portTICK_RATE_MS;

  // To makro jest konieczne.
    crSTART( xHandle );

  for( ;; )
    {
         // Czekaj 200ms.
	      crDELAY( xHandle, xDelayTime );

     // Wykonaj inne czynności.
       }

  // To makro jest konieczne.
    crEND();
    }
    ';
    $language = 'C++';
    $geshi = new GeSHi($source, $language);
    echo $geshi->parseCode();
    ?>
    <h4>Wysyłanie wiadomości do bufora</h4>
    W poniższym kodzie jest 1 kolejka, w zadaniu należy pracować z tablica kolejek. Kolejka o indeksie x odpowiada korutynie x.
    <?php
    $source = '
    xQueueHandle xCoRoutineQueue;

portSHORT main( void )
{

  hardwareInit();
    xSerialPortInitMinimal(16);

  xCoRoutineQueue = xQueueCreate(4, 1);

  xCoRoutineCreate(vKlawisze, 0, 0);
    xCoRoutineCreate(vDioda, 0, 0);
      xCoRoutineCreate(vDioda, 0, 1);

  vTaskStartScheduler();
    return 0;
    }

 // Korutyna, która wysyła wiadomości do bufora, a następnie odczekuje.
  static void prvCoRoutineFlashTask( xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex )
   {
      // Te zmienne muszą być statyczne. W przeciwnym razie zostaną umieszczone na stosie.
         // Inna korutyna korzysta z tego samego stosu, przez co zmienne mogą być zmienione po powrocie do korutyny.
	    // Ze zmiennych na stosie możemy korzystać pod warunkiem, że przez ten czas nie wykonamy makra,
	       // które przełączy wykonywaną korutynę
	          static portBASE_TYPE xNumberToPost = 0;
		     static portBASE_TYPE xResult;

  // To makro jest konieczne.
    crSTART( xHandle );

  for( ;; )
    {
        // Modyfikacja wiadomości, która zostanie wysłana.
	    xNumberToPost++;

    // Wcześniej kolejka musi zostać utworzona, np. w funkcji main
        crQUEUE_SEND( xHandle, xCoRoutineQueue, &xNumberToPost, NO_DELAY, &xResult );

    if( xResult != pdPASS )
        {
	        // The message was not posted!
		    }


    // Oczekiwanie 100 taktów systemu operacyjnego
        crDELAY( xHandle, 100 );
	  }

  // To makro jest konieczne.
    crEND();
    }

// Proces bezczynności obsługuje korutyny.
// W tym celu trzeba napisać dla niego taką funkcję oraz zmodyfikować odpowiednie ustawienia w projekcie
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

<h3>Zadanie 6 - obsługa diody</h3>
<p>
Obsługa diody. Na poniższym rysunku przedstawiono algorytm do obsługi diody. Nie stosujemy tam makra <b>crDELAY</b>, ponieważ w tym czasie nie moglibyśmy czyać z bufora. Zamiast tego wykonujemy operację odczytu z bufora (makro to zostało opisane na końcu instrukcji. Jako parametr timeout ustawiamy wartość, na jaką ma być zapalona dioda lub wartość <b>portMAX_DELAY</b>, gdy dioda jest zgaszona. Po odebraniu wiadomości zapalamy lub gasimy diodę (odebrano wiadomość 0), a następnie odbieramy kolejną wiadomość. Operacja odbioru wiadomości nie może przekraczać parametru timeout, którego sposób wyznaczania został opisany na początku paragrafu.
<br><img src="http://adam.kaliszan.yum.pl/wyklady/LabSop/korutynyZad6a.png"></img>
</p>
<h4>Odbieranie wiadomości z bufora</h4>
<?php
$source = '
// Korutyna odbiera informację o diodzie, którą ma zapalić (nr diody)
// Do momentu odebrania wiadomości korutyna jest zablokowana
static void prvCoRoutineFlashWorkTask( xCoRoutineHandle xHandle, unsigned portBASE_TYPE uxIndex )
{
  // Te zmienne muszą być statyczne. W przeciwnym razie zostaną umieszczone na stosie.
    // Inna korutyna korzysta z tego samego stosu, przez co zmienne mogą być zmienione po powrocie do korutyny.
      // Ze zmiennych na stosie możemy korzystać pod warunkiem, że przez ten czas nie wykonamy makra,
        // które przełączy wykonywaną korutynę
	  static portBASE_TYPE xResult;
	    static unsigned portBASE_TYPE uxLEDToFlash;

  // To makro jest konieczne.
    crSTART( xHandle );

  for( ;; )
    {
        // Oczekiwanie, na odebranie wiadomości z bufora xCoRoutineQueue
	    crQUEUE_RECEIVE( xHandle, xCoRoutineQueue, &uxLEDToFlash, portMAX_DELAY, &xResult );

    // Sprawdzanie, czy odebrano coś z korutyny, czy został przekroczony zadany czas odczytu z bufora
        if( xResult == pdPASS )
	    {
	          // Odebrano wiadomość. Zmiana stanu diody, której numer określono w wiadomości
		        vParTestToggleLED( uxLEDToFlash );
			    }
			      }
			        // To makro jest konieczne.
				  crEND();
				  }
				  ';
				  $language = 'C++';
				  $geshi = new GeSHi($source, $language);
				  echo $geshi->parseCode();
				  ?>

<h3>Zadanie 7 - wprowadzenie do systemu rozproszonego</h3>
<p>
Program można udoskonalić:
 <ul>
   <li>dodając opcję wyłączania diody, jeśli przycisk jest wciśnięty przez dłuższy czas; Konieczna jest wtedy implementacja automatu stanu przycisku,</li>
     <li>dodając kolejną korutynę, która obsługuje komunikację przez magistralę RS485; Korutyna ta po odebraniu odpowiedniego rozkazu może również umieszczać wiadomość w buforze, by zapalić diodę na określony czas.</li>
      </ul>
      </p>
      <br><img src="http://adam.kaliszan.yum.pl/wyklady/LabSop/korutynyZad6full.png"></img>


<h3>Literatura</h3>
<ul>
 <li>Dokumentacja systemu FreeRtos http://www.freertos.org/</li>
 </ul>

