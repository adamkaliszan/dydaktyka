#include <pthread.h>
#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <string.h>

#define ROZMIAR_BUFORA 256

struct buforCykliczny;

struct wiadomosc
{
  //char tresc[50];                   //Treść wiadomości nie może przekraczać 49 znaków
  int liczba;
};

struct buforCykliczny
{
  struct wiadomosc tablica[ROZMIAR_BUFORA];  //Tablica z wiadomościami. Z niej są one odczytywane i zapisywane

  int    licznik;                  //Określa liczbę elementów zapisaną w buforze
  int    we;                       //Określa indeks zapisu do tablicy
  int    wy;                       //Określa indeks odczytu z tablicy
  pthread_mutex_t *sem;
  pthread_cond_t *cond;
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
  struct buforCykliczny *bufor = (struct buforCykliczny*)(arg);
  struct wiadomosc locWiad; 

  int i;                               //Niektóre odmiany C nie pozwalają na kod: for(int i=0 ...
  for (i=0; i<20; i++)                 //Wysyłanie 20 wiadomości
  {
    locWiad.liczba = i;
    zapiszKryt(bufor, &locWiad);
    sleep(1);
  }
  return NULL;
}

void *posrednikFunc(void *arg)
{
  struct wiadomosc locWiad;
  struct buforCykliczny **bufory = (struct buforCykliczny **)arg;

  struct buforCykliczny *bProdPosr = bufory[0];
  struct buforCykliczny *bPosrKons = bufory[1];

  int i;
  for (i=0; i<20; i++)
  {
    czytajKryt(bProdPosr, &locWiad);
    //printf("Odebrałem wiadomość %s\n", locWiad.tresc);
    locWiad.liczba *=2;
    zapiszKryt(bPosrKons, &locWiad);
  }
  return NULL;
}


void *konsumentFunc(void *arg)
{
  struct buforCykliczny *bufor = (struct buforCykliczny*)(arg);
  struct wiadomosc locWiad;

  int i;
  for (i=0; i<20; i++)
  {
    czytajKryt(bufor, &locWiad);
    printf("Odebrałem wiadomość %d\n", locWiad.liczba);
  }
  return NULL;
}



int main()
{
  pthread_mutex_t semProdPosr = PTHREAD_MUTEX_INITIALIZER;
  pthread_mutex_t semPosrKons = PTHREAD_MUTEX_INITIALIZER;
  struct buforCykliczny buforProdPosr;
  struct buforCykliczny buforPosrKons;
  pthread_cond_t condProdPosr = PTHREAD_COND_INITIALIZER;
  pthread_cond_t condPosrKons = PTHREAD_COND_INITIALIZER;

  memset(&buforProdPosr, 0, sizeof (struct buforCykliczny));
  buforProdPosr.sem = &semProdPosr;
  buforProdPosr.cond = &condProdPosr;

  memset(&buforPosrKons, 0, sizeof (struct buforCykliczny));
  buforPosrKons.sem = &semPosrKons;
  buforPosrKons.cond = &condPosrKons;

  pthread_t producent;                 // Uchwyt do wątku producenta
  pthread_t posrednik;                 // Uchwyt do wątku producenta
  pthread_t konsument;                 // Uchwyt do wątku konsumenta

  if ( pthread_create(&producent, NULL, producentFunc, (void *)(&buforProdPosr)))
  {
    printf("Błąd przy tworzeniu wątku producenta\n");
    abort();
  }

  struct buforCykliczny *bufory[] = {&buforProdPosr, &buforPosrKons};

  if ( pthread_create(&posrednik, NULL, posrednikFunc, bufory))
  {
    printf("Błąd przy tworzeniu wątku producenta\n");
    abort();
  }

  if ( pthread_create(&konsument, NULL, konsumentFunc, (void *)(&buforPosrKons)))
  {
    printf("Błąd przy tworzeniu wątku konsumenta\n");
    abort();
  }


  if ( pthread_join (producent, NULL ) ) 
  {
    printf("Błąd w kończeniu wątku producenta\n");
    exit(0);
  }
  if ( pthread_join (posrednik, NULL ) ) 
  {
    printf("Błąd w kończeniu wątku producenta\n");
    exit(0);
  }
  if ( pthread_join (konsument, NULL ) ) 
  {
    printf("Błąd w kończeniu wątku konsumenta\n");
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
  pthread_mutex_lock(bufor->sem); 

  while (bufor->licznik == 0)
    pthread_cond_wait(bufor->cond, bufor->sem);

  struct wiadomosc *wiadZrodlo = &bufor->tablica[bufor->wy];
  memcpy(wiadCel, wiadZrodlo, sizeof(struct wiadomosc));

  bufor->licznik = bufor->licznik - 1;
  bufor->wy      = (bufor->wy +1) % ROZMIAR_BUFORA;

  pthread_mutex_unlock(bufor->sem); 
  pthread_cond_signal (bufor->cond);
  return 0;
}

int zapiszKryt(struct buforCykliczny *bufor, struct wiadomosc *wiadZrodlo)
{
  pthread_mutex_lock(bufor->sem); 
  while (bufor->licznik == ROZMIAR_BUFORA)
    pthread_cond_wait(bufor->cond, bufor->sem);

  struct wiadomosc *wiadCel = &bufor->tablica[bufor->we];
  memcpy(wiadCel, wiadZrodlo, sizeof(struct wiadomosc));
  bufor->licznik = bufor->licznik + 1;
  bufor->we      = (bufor->we +1) % ROZMIAR_BUFORA;

  pthread_mutex_unlock(bufor->sem); 
  pthread_cond_signal (bufor->cond);
  return 0;
}
