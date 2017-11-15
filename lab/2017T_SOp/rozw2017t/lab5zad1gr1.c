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

int main()
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
  return 0;
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
  int wynik;
  do
  {
    pthread_mutex_lock(&semafor); 
    wynik = czytaj(bufor, wiadCel);
    pthread_mutex_unlock(&semafor); 
  } while (wynik == 1);
  return wynik;
}

int zapiszKryt(struct buforCykliczny *bufor, struct wiadomosc *wiadZrodlo)
{
  int wynik;
  do
  {
    pthread_mutex_lock(&semafor); 
    wynik = zapisz(bufor, wiadZrodlo);
    pthread_mutex_unlock(&semafor); 
  } while (wynik == 1);
  return wynik;
}

