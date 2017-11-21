<?php
include_once '../../class.geshi.php';
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<meta name="GENERATOR" content="Microsoft FrontPage 4.0">
<meta name="ProgId" content="FrontPage.Editor.Document">
<title>Zajęcia labaratoryjne nr 5 z przedmiotu Systemy Operacyjne</title>
</head>
<body>

<h1>Funkcje systemowe służące do synchronizacji wątków</h1>
<p>
Przetwarzanie realizowane przez wątki musi być odpowiednio synchronizowane, tak by wykonując operacje na wspólnych strukturach danych nie dopuścić do niespójności danych. Stosowane są dwie metody zapewnienia odpowiedniej koordynacji wątków: korzystanie z zamków (semaforów binarnych czyli blokad wzajemnie wykluczających, dalej nazywanych muteksami) lub korzystanie z konstrukcji nazywanych zmiennymi warunkowymi (w tej konstrukcji mamy zamek oraz zmienną warunkową).
</p>
<h3>Zamki</h3>
<p>
Zmienną reprezentującą zamek zwaną dalej "muteksową" można porównać do semafora binarnego, który wątki mogą posiadać. Mutex albo zezwala na dostęp, albo go zabrania. Zamknięcia muteksu może dokonać dowolny wątek znajdujący się w jego zasięgu, natomiast otworzyć go może tylko wątek który go zamknął (jeśli program jest poprawnie zaimplementowany). Wątki, które nie mogą uzyskać dostępu do muteksu są blokowane w oczekiwaniu na niego. Operacje wykonywane na muteksach są niepodzielne. Jeśli za pomocą muteksów trzeba synchronizować wątki kilku procesów, należy odwzorować muteks w obszar pamięci współdzielonej dostępny dla wszystkich procesów.
</p>
<p>
Do zapewnienia wzajemnego wykluczania używana jest zmienna (o przykładowej nazwie <i>mutex</i>), zadeklarowana następująco:
</p>
<?php
$source = '
pthread_mutex_t mutex = PTHREAD_MUTEX_INITIALIZER;';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
Zmienna ta umożliwia zajmowanie oraz zwalnianie zamka.
</p>
<p>
Zajęcie zamka polega na sprawdzeniu jego stanu oraz zajęciu, jeśli był on wolny. W przeciwnym przypadku wątek zostanie zawieszony do momentu zwolnienia zamka. Operacja sprawdzenia i zajęcia zamka jest operacją atomową (wykonaną niepodzielnie). Funkcja <b>pthread_mutex_lock</b> powoduje zajęcie zamka wskazywanego przez parametr <i>mutex</i>. Przykład zastosowania funkcji przedstawiono poniżej.
</p>
<?php
$source = '
pthread_mutex_lock ( pthread_mutex_t *mutex )';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
Zwolnienie zamka polega na jego otwarciu. Po wykonaniu tej operacji wznawiane są automatycznie, bez interwencji programisty, wątki czekające na otwarcie zamka. Funkcja <b>pthread_mutex_unlock</b> powoduje zwolnienie zamka. Przykład zastosowania funkcji przedstawiono poniżej.
</p>
<?php
$source = '
pthread_mutex_unlock ( pthread_mutex_t *mutex )';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
Proces może podjąc również próbę zajęcia zamka. Jeśli zamek jest zajęty, to próba kończy się niepowodzeniem, a wątek kontunuuje swoje działanie (nie jest zawieszany do czasu zwolnienia zamka, tak by mógł go zająć. Do wykonania tej czynności służy funkcja <b>pthread_mutex_trylock</b>, której przykład zastosowania przedstawiono poniżej.
</p>
<?php
$source = '
pthread_mutex_trylock ( pthread_mutex_t *mutex )';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>

<h2>Zadanie 1</h2>
<ol>
 <li>Napisz program, w którym wątek producenta komunikuje się z wątkiem konsumenta. Wątek producenta generuje liczby pierwsze, natomiast wątek konsumenta je wypisuje na ekranie. Zastosuj tylko semafory</li>
 <li>Rozwiąż ten sam problem stosując semafory binarne</li>
</ol>
<h3>Podpowiedź</h3>
<p>
Zauważmy, że zarówno operacja zapisu, jak i odczytu z bufora musi zostać wykonana niepodzielnie, a zatem w sekcji krytycznej programu. 
W tym celu należy utworzyć semafor, który blokuje jednoczesnego dostępu do wielu sekcji krytycznych. Przedstawiony poniżej program napisany jest w zły sposób. Może w nim dojść do szkodliwej rywalizacji. By rozwiązać ten problem należy zaimplementować i korzystać z funkcji
<ul>
 <li>int <b>czytajKryt</b>(struct buforCykliczny *bufor, struct wiadomosc *wiadCel);</li>
 <li>int <b>zapiszKryt</b>(struct buforCykliczny *bufor, struct wiadomosc *wiadZrodlo);</li>
</ul>
</p>
<?php
$source = '
#include <pthread.h>
#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <string.h>

#define ROZMIAR_BUFORA 256

//Semafor do synchronizacji wątków, korzystających ze wspólnej struktury bufora cyklicznego.
pthread_mutex_t semafor = PTHREAD_MUTEX_INITIALIZER;

struct wiadomosc
{
  char tresc[50];                   //Treść wiadomości nie może przekraczać 49 znaków
};

struct buforCykliczny
{
  struct wiadomosc tablica[ROZMIAR_BUFORA];  //Tablica z wiadomościami. Z niej są one odczytywane i zapisywane

  int    licznik;                  //Określa liczbę elementów zapisaną w buforze
  int    we;                       //Określa indeks zapisu do tablicy
  int    wy;                       //Określa indeks odczytu z tablicy
};

/* Deklaracje funkcji */
int   czytaj        (struct buforCykliczny *bufor, struct wiadomosc *wiadCel);
int   zapisz        (struct buforCykliczny *bufor, struct wiadomosc *wiadZrodlo);
int   czytajKryt    (struct buforCykliczny *bufor, struct wiadomosc *wiadCel);
int   zapiszKryt    (struct buforCykliczny *bufor, struct wiadomosc *wiadZrodlo);
void* producentFunc (void *arg);
void* konsumentFunc (void *arg);

void *producentFunc(void *arg)
{
  /* Odczytywanie z argumentu wskaźnika do bufora.
     Konieczne jest rzutowanie.
     Mechanizm niebezpieczny!!!, z którego rezygnuje się we współczesnych językach, jest one jednak bardzo szybki.
     Funkcja musi "wiedzieć" jakiego typu jest obiekt pod adresem arg.
  */
  struct buforCykliczny *bufor = (struct buforCykliczny*)(arg);

  struct wiadomosc locWiad;            //Lokalna struktura, w której zapisane są wygenerowane wiadomości.

  int i;                               //Niektóre odmiany C nie pozwalają na kod: for(int i=0 ...
  for (i=0; i<20; i++)                 //Wysyłanie 20 wiadomości
  {
    /* Zapis do tablicy w struktorze wiadomość. 
       funkcja sprintf robi to samo co printf, tylko pisze pod podany adres pamięci,
       a nie na ekran 
    */
    sprintf(locWiad.tresc, "Wiadomość nr %d", i);

    while(zapiszKryt(bufor, &locWiad)) //Próba zapisu wiadomości ponawiana jest tak długo,
      ;                                //dopóki nie zakończy się sukcesem. Wtedy funkcja zwróci 0

    sleep(1);                          //Celowe opóźnianie działania producenta, by na ekranie było widać efekty komunikacji
  }
  return NULL;                         //Wątek wykonał się poprawnie
}

void *konsumentFunc(void *arg)
{
  struct buforCykliczny *bufor = (struct buforCykliczny*)(arg);
  struct wiadomosc locWiad;

  int i;
  for (i=0; i<20; i++)
  {
    while(czytajKryt(bufor, &locWiad)) //Próba odczytu wiadomości ponawiana jest tak długo,
      ;                                //dopóki nie zakończy się sukcesem. Wtedy funkcja zwróci 0
    printf("Odebrałem wiadomość %s\n", locWiad.tresc);

    sleep(1);
  }
  return NULL;
}
main()
{
  struct buforCykliczny buforDanych;
  /* Zapisanie zerami całej struktury buforDanych */
  memset(&buforDanych, 0, sizeof (struct buforCykliczny));

  /* Dzięki temu ten kod jest zbędny:
  buforDanych.licznik=0;
  buforDanych.we=0;
  buforDanych.wy=0;
  */

  pthread_t konsument;                 // Uchwyt do wątku konsumenta
  pthread_t producent;                 // Uchwyt do wątku producenta

  if ( pthread_create(&konsument, NULL, konsumentFunc, (void *)(&buforDanych)))
  {
    printf("Błąd przy tworzeniu wątku konsumenta\n");
    abort();
  }

  if ( pthread_create(&producent, NULL, producentFunc, (void *)(&buforDanych)))
  {
    printf("Błąd przy tworzeniu wątku producenta\n");
    abort();
  }

  if ( pthread_join (konsument, NULL ) ) 
  {
    printf("Błąd w kończeniu wątku konsumenta\n");
    exit(0);
  }
  /* w tym momencie zakończył się również wątek producenta i nie ma potrzeby na niego czekać.
     kolejne 5 linijek kodu jest zbędnych */

  if ( pthread_join (producent, NULL ) ) 
  {
    printf("Błąd w kończeniu wątku producenta\n");
    exit(0);
  }
  exit(0);
}

int czytaj(struct buforCykliczny *bufor, struct wiadomosc *wiadCel)
{
  if (bufor->licznik == 0)
    return 1;                          //bufor jest pusty, nie ma czego czytać

  /* ----- Kopiowanie wiadomości z bufora pod adres pamięci wiadCel ----- */
  /* Ustawiamy wskaźnik na tablicę wiadomości w buforze, którą chcemy odczytać (przekopiować pod adres wiadCel */
  struct wiadomosc *wiadZrodlo = &bufor->tablica[bufor->wy];

  /* Kopiowanie wiadomości do bufora. Funkcja mamcpy zawarta jest w bibliotece string.h */
  memcpy(wiadCel, wiadZrodlo, sizeof(struct wiadomosc));

  /* Uaktualnianie stanu bufora */
  bufor->licznik = bufor->licznik - 1;
  bufor->wy      = (bufor->wy +1) % ROZMIAR_BUFORA;

  return 0;                            //funkcja zwraca informację o poprawnym zapisie do bufora
}

int zapisz(struct buforCykliczny *bufor, struct wiadomosc *wiadZrodlo)
{
  if (bufor->licznik == ROZMIAR_BUFORA)
    return 1;                          //bufor jest zapełniony, nie ma miejsca na kolejną wiadomość

  /* ----- Kopiowanie nowej wiadomości (wadZrodlo) do bufora. ----- */
  /* Ustawiamy wskaźnik na tablicę wiadomości w buforze, pod którą zostanie zapisana nowa wiadomość*/
  struct wiadomosc *wiadCel = &bufor->tablica[bufor->we];

  /* Kopiowanie wiadomości do bufora. Funkcja mamcpy zawarta jest w bibliotece string.h */
  memcpy(wiadCel, wiadZrodlo, sizeof(struct wiadomosc));

  /* ----- Uaktualnianie stanu bufora.                        ----- */
  bufor->licznik = bufor->licznik + 1;
  bufor->we      = (bufor->we +1) % ROZMIAR_BUFORA;

  return 0;                            //funkcja zwraca informację o poprawnym zapisie do bufora
}
/* Ta część kodu musi zostać zmodyfikowana.
   Należy wprowadzić obsługę semaforów.
   Uwaga na zakleszczenia !!!!!!!
*/
int czytajKryt(struct buforCykliczny *bufor, struct wiadomosc *wiadCel)
{
  return czytaj(bufor, wiadCel);
}

int zapiszKryt(struct buforCykliczny *bufor, struct wiadomosc *wiadZrodlo)
{
  return zapisz(bufor, wiadZrodlo);
}';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>

Rozwiązania i podpowiedzi z poprzednich zajęć.
<ul>
 <li>Grupa z poniedziałku o godzinie 9:45 <a href="rozw2017t/lab4gr1.c">lab4gr1.c</a>
 <li>Grupa z wtorku o godzinie 11:45 <a href="rozw2017t/lab4gr2.c">lab4gr2.c</a>
 <li>Grupa z piątku o godzinie 9:45 <a href="rozw2017t/lab4gr3.c">lab4gr3.c</a>
 <li>Grupa z piątku o godzinie 13:30 <a href="rozw2017t/lab4gr4.c">lab4gr4.c</a>
</ul>

<h2>Zadanie 2</h2>
Napisz program, w którym trzy wątki (A, B, C) komunikują się ze sobą za pośrednictwem dwóch buforów. Wątek A generuje liczby pierwsze, Wątek B mnoży je przez 2, Wątek C wpisuje je na ekranie. Program należy napisać zgodnie z regułami sztuki.

<h2>Zmienne warunkowe</h2>
<p>
Kiedy niezbędne jest synchronizowanie wątków za pomocą bieżących wartości danych chronionych muteksami, można użyć konstrukcji nazywanych zmiennymi warunkowymi. Wątek uśpiony, który oczekuje spełnienie zadanego warunku zostanie wybudzony po zmienie wartości zmiennej warunkowej. Zmienna warunkowa jest kojarzona z konkretnym muteksem. Przed sprawdzaniem wartości tej zmiennej należy opuścić semafor. Jeśli wartość zmiennej warunkowej nie pozwala na dalsze wykonywanie wątku, wątek zostaje zawieszony, a semafor (mutex) który został zablokowany przez ten wątek zostaje odblokowany. Działanie wątku zostaje wznowione automatycznie po tym jak zostanie wysłany sygnał o zmianie wartości zmiennej warunkowej. Wraz ze wznowieniem wykonywania wątku opusdzczony zostaje semafor. Wątek po wyjściu z sekcji krytycznej musi zatem zwolnić semafor.
</p>
<p>
Zmienną warunkową (tutaj o nazwie <b>cond</b>) możemy utworzyć w następujący sposób:
</p>
<?php
$source = '
pthread_cond_t cond = PTHREAD_COND_INITIALIZER;';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<h4>Oczekiwanie na sygnał</h4>
<p>
Załóżmy, że wątek wchodzi do sekcji krytycznej, a następnie sprawdza pewien warunek (np. dostępne miejsce w buforze). Jeśli warunek nie jest spełniony, wątek może zwolnić semafor i uśpić się na jakiś czas. Podejście takie jest mało eleganckie. Marnotrawione są cykle procesora. Dodatkowo, wątek nie będzie wybudzony natychmiast po spełnieniu tego warunku. Dlatego też powstała funkcja <b>pthread_cond_wait</b>. Powoduje uśpienie wątku na zmiennej warunkowej, wskazywanej przez parametr <i>cond</i>. Na czas uśpienia wątek zwalnia zamek, wskazywany przez parametr <i>mutex</i>, udostępniając tym samym sekcję krytyczną innym wątkom.
</p>
<?php
$source = '
pthread_cond_wait ( pthread_cond_t *cond, pthread_mutex_t *mutex )';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
Po obudzeniu i wyjściu z funkcji (na skutek odebraniu sygnału wysłanego przez <b>pthread_cond_signal</b>) zamek zajmowany jest ponownie.
</p>
<?php
$source = '
pthread_cond_signal ( pthread_cond_t *cond )';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
Po wysłanie sygnału (obudzenie) do jednego z oczekujących na zmiennej warunkowej wskazywanej przez <i>cond</i> wątków, wątek taki zostaje wznowiony. Przykładem wysłania sygnału może być proces konsumenta, który odebrał dane z bufora i przez to w buforze jest miejcce do umieszczenie kolejnych danych przez wątek producenta. Obok funkcji <b>pthread_cond_signal</b> istnieje również funkcja <b>pthread_cond_brodcast</b>, która dostarcza sygnał wszystkim wątkom (a nie tylko temu, który pierszy oczekiwał na zmienną warunkową) informacji o zmianie zmiennej warunkowej. Tak wznowiony wątek sprawdza w swojej sekcji krytycznej czy odpowiedni warunek jest spełniony.
</p>

<h2>Zadanie 3</h2>
<p>
Zmodyfikuj program napisany w zadaniu 1. Zastosuj tam zmienne warunkowe. Porównaj działanie obu programów pod względem zapotrzebowania na zasoby procesora. W tym celu uruchom program równlegle z programem top.
<p>
<pre>
./zadanie3 & top
</pre
<h3>Podpowiedź</h3>
<p>Zadanie najłatwiej rozwiązać w nastepujący sposób. Jako punkt wyjścia przyjmijmy kod podany na początku instrukcji, w którym są funkcje  czytaj i czytaj kryt. Do czytaj</p>

<h2>Rozwiązania</h2>
Rozwiązania i podpowiedzi z poprzednich zajęć.
<ul>
 <li>Grupa z poniedziałku o godzinie 9:45 <a href="rozw2017t/lab5zad1gr1.c">lab5zad1gr1.c</a>
 <li>Grupa z poniedziałku o godzinie 9:45 <a href="rozw2017t/lab5zad2gr1.c">lab5zad2gr1.c</a>
 <li>Grupa z wtorku o godzinie 11:45 <a href="rozw2017t/lab5gr2.c">lab5gr2.c</a>
 <li>Grupa z piątku o godzinie 9:45 <a href="rozw2017t/lab5gr3.c">lab5gr3.c</a>
 <li>Grupa z piątku o godzinie 13:30 <a href="rozw2017t/lab5gr4.c">lab5gr4.c</a>
</ul>

