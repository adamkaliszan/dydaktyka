<?php
include_once '../../class.geshi.php';
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<meta name="GENERATOR" content="Microsoft FrontPage 4.0">
<meta name="ProgId" content="FrontPage.Editor.Document">
<title>Zajęcia labaratoryjne nr 4 z przedmiotu Systemy Operacyjne</title>
</head>
<body>
<h1>Tworzenie i obsługa wątków</h1>
<p>Wątki określane są jako wydzielone sekwencje przewidzianych do wykonania instrukcji, które są realizowane w ramach jednego procesu. Każdy wątek ma swój własny stos, zestaw rejestrów, licznik programowy, indywidualne dane, zmienne lokalne i informacje o stanie. Wszystkie wątki danego procesu mają jednak tę samą przestrzeń adresową, ogólną obsługę sygnałów, pamięć wirtualną, dane oraz wejście-wyjście. W ramach procesu wielowątkowego każdy wątek wykonuje się oddzielnie i asynchronicznie.
</p>
<p>
Wszystkie programy korzystające z funkcji operujących na wątkach normy POSIX zawierają dyrektywę 
</p>
<?php
$source = '
#include <pthread.h>';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p> 
Kompilując przy użyciu <b>gcc</b> programy korzystające z tej biblioteki, należy wymusić jej dołączenie, przez użycie opcji <tt>-lpthread</tt>:
</p>
<pre>gcc -lpthread program.c
</pre>
<p>
Każdy proces zawiera przynajmniej jeden główny wątek początkowy, tworzony przez system operacyjny w momencie stworzenia procesu. By dodać do procesu nowy wątek należy wywołać funkcję <b>pthread_create</b>. Nowo utworzony wątek zaczyna się od wykonania funkcji użytkownika (przekazanej mu przez argument <b>pthread_create</b>). Wątek działa aż do czasu wystąpienia jednego z następujących zdarzeń:
<ul>
 <li>zakończenia funkcji,</li>
 <li>wywołania funkcji <b>pthread_exit</b>,</li>
 <li>anulowania wątku za pomocą funkcji <b>pthread_cancel</b>,</li>
 <li>zakończenia procesu macierzystego wątku,</li>
 <li>wywołania funkcji <b>exec</b> przez jeden z wątków.</li>
</ul>
</p>
<h3>Funkcje systemowe służące do tworzenia wątków i ich obsługi</h3>
<h4>Tworzenie wątku</h4>
<?php
$source = '
pthread_create(pthread_t *thread, pthread_attr_t *attr, void* (*start_routine) (void*),void *arg)';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
utworzenie wątku. Wątek wykonuje funkcję wskazywaną przez parametr <i>start_routine.</i> Parametry funkcji muszą być przekazane przez wskaźnik na obszar pamięci (strukturę), który zawiera odpowiednie wartości. Wskaźnik ten jest przekazywany przez parametr <i>arg</i> i jest dalej przekazywany jako parametr aktualny do funkcji wykonywanej przez wątek. Parametr <i>attr</i> wskazuje na atrybuty wątku, a przez wskaźnik <i>thread</i> zwracany jest identyfikator wątku.
</p>
<h4>Kończenie wątku</h4>
<?php
$source = '
pthread_exit(void *retval)';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
Funkcja powoduje zakończenie wątku i przekazanie<i>retval</i>, jako wskaźnika na wynik. Wskaźnik ten może zostać przejęty przez inny wątek, który będzie wykonywał funkcję <b>pthread_join.</b>
</p>
<h4>Oczekiwanie na zakończenie wątku</h4>
<?php
$source = '
pthread_join(pthread_t th, void **thread_return)';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
oczekiwanie na zakończenie wątku. Funkcja umożliwia zablokowanie wątku w oczekiwaniu na zakończenie innego wątku, identyfikowanego przez parametr <i>th</i>. Jeśli oczekiwany wątek zakończył się wcześniej, funkcja zakończy się natychmiast. Funkcja przekazuje przez parametr <i>thread_return </i>wskaźnik na wynik wątku (wykonywanej przez niego funkcji), przekazany jako parametr funkcji <b>pthread_exit </b>wywołanej w zakończonym wątku.
</p>
<h4>Zakończenie wykonywania innego wątku</h4>
<?php
$source = '
pthread_cancel(pthread_t thread)';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
Funkcja umożliwia wątkowi usunięcie z systemu innego wątku, identyfikowanego przez parametr <i>thread</i>.
</p>
<h2>Zadanie 1</h2>
<p>
Napisz program, w którym utworzone zostaną dwa wątki: wątek producenta i wątek konsumenta. Wątek producenta wysyła do bufora kolejne liczby z ciągu Fibonacciego. Po wysłaniu każdej z liczb, wątek usypiany jest na 1 sekundę. Wątek konsumenta odbiera te liczby i wypisuje na ekranie. Zaimplementuj bufor cykliczny, który pośredniczy w wymianie wiadomości. Program napisz niezgodnie z regułami sztuki. Pomiń zagadnienia związane z synchronizacją oraz zignoruj problem szkodliwej rywalizacji (wyścigu warunków). Problemy te zostaną rozwiązane w następnym zadaniu.
</p>
<h4>Podpowiedź</h4>
<p>
Program, w którym utworzono 2 wątki:
<?php
$source = '
#include<stdio.h>
#include<pthread.h>
#include<stdlib.h>
#include<unistd.h>
#include<sys/wait.h>

void* funProd(void *arg);
void* funKons(void *arg);

int main()
{
  pthread_t producent;
  pthread_t konsument;


  pthread_create(&producent, NULL, funProd, NULL);
  pthread_create(&konsument, NULL, funKons, NULL);

  pthread_join(producent, NULL);
  pthread_join(konsument, NULL);
  return 0;
}

void* funProd(void *arg)
{
  int i;
  for (i=0; i<20; i++)
  {
    sleep(1);
    printf("*\n");
  }
}

void* funKons(void *arg)
{
  int i;
  for (i=0; i<20; i++)
  {
    sleep(1);
    printf("$\n");
  }
}
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Implementacja bufora cyklicznego
<p>
<?php
$source = '
#include <pthread.h>
#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <string.h>

#define ROZMIAR_BUFORA 256

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

int czytaj(struct buforCykliczny *bufor, struct wiadomosc *wiadCel)
{
  if (bufor->licznik == 0)
    return 1;                      //bufor jest pusty, nie ma czego czytać

  /* ----- Kopiowanie wiadomości z bufora pod adres pamięci wiadCel ----- */
  /* Ustawiamy wskaźnik na tablicę wiadomości w buforze, którą chcemy odczytać (przekopiować pod adres wiadCel */
  struct wiadomosc *wiadZrodlo = &bufor->tablica[bufor->wy];

  /* Kopiowanie wiadomości do bufora. Funkcja mamcpy zawarta jest w bibliotece string.h */
  memcpy(wiadCel, wiadZrodlo, sizeof(struct wiadomosc));

  /* Uaktualnianie stanu bufora */
  bufor->licznik = bufor->licznik - 1;
  bufor->wy      = (bufor->wy +1) % ROZMIAR_BUFORA;

  return 0;                        //funkcja zwraca informację o poprawnym zapisie do bufora
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
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>



