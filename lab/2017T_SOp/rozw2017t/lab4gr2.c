#include <pthread.h>
#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <string.h>

#define ROZMIAR_BUFORA 16

void* funProd(void *arg);
void* funKons(void *arg);


struct buforCykliczny
{
  int    tablica[ROZMIAR_BUFORA];  //Tablica z wiadomościami. Z niej są one odczytywane i zapisywane

  int    licznik;                  //Określa liczbę elementów zapisaną w buforze
  int    we;                       //Określa indeks zapisu do tablicy
  int    wy;                       //Określa indeks odczytu z tablicy
};

int czytaj(struct buforCykliczny *bufor)
{
  int liczba;

  while (czytaj2(buf, &liczba) == 1)
    ;

}

int czytaj2(struct buforCykliczny *bufor, int *liczba);
{
  //opuść semafor
  int wynik;
  if (bufor->licznik == 0)
    wynik = 1;
  else
  {
    wynik = 0;

    *liczba = bufor->tablica[bufor->wy];

    bufor->licznik = bufor->licznik - 1;
    bufor->wy      = (bufor->wy + 1) % ROZMIAR_BUFORA;
  }
  //podnieś semafor
  return wynik;



void zapisz(struct buforCykliczny *bufor, int wartosc)
{
  //opuść semafor
  while (bufor->licznik == ROZMIAR_BUFORA)
    ;
  bufor->tablica[bufor->we] = wartosc;
  bufor->licznik = bufor->licznik + 1;
  bufor->we      = (bufor->we +1) % ROZMIAR_BUFORA;
  //podnieś semafor
}


struct buforCykliczny buf;


int main()
{
  memset(&buf, 0, sizeof(struct buforCykliczny));

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
    zapisz(&buf, i);
    sleep(1);
  }
}

void* funKons(void *arg)
{
  int i;
  for (i=0; i<20; i++)
  {
    int x = czytaj(&buf);
    printf("x = %d\n", x);
  }
}

