#include<stdio.h>
#include<pthread.h>
#include<stdlib.h>
#include<unistd.h>
#include<sys/wait.h>
#include<string.h>

#define ROZMIAR 16

void* funProd(void *arg);
void* funKons(void *arg);

struct wiadomosc
{
  int wartosc;
};


struct bufor
{
  struct wiadomosc wiadomosci[ROZMIAR];

  int indWe;
  int indWy;
  int licznik;
};

typedef struct bufor bufor_t;


int main()
{
  bufor_t MyBuf;
  memset(&MyBuf, 0, sizeof(bufor_t));

  pthread_t producent;
  pthread_t konsument;

  struct wiadomosc *ptrWiad = malloc(sizeof(struct wiadomosc));

  ptrWiad->wartosc=10;


  pthread_create(&producent, NULL, funProd, &MyBuf);
  pthread_create(&konsument, NULL, funKons, &MyBuf);

  pthread_join(producent, NULL);
  pthread_join(konsument, NULL);
  return 0;
}

void zapisz(struct bufor* buf, int wart)
{
  while(buf->licznik == ROZMIAR) ;

  buf->wiadomosci[buf->indWe].wartosc=wart;
  buf->licznik++;
  buf->indWe++;
  buf->indWe %= ROZMIAR;
}

int odczyt(struct bufor *buf)
{
  while(buf->licznik == 0) ;

  int wynik = buf->wiadomosci[buf->indWy].wartosc;
  buf->licznik--;
  buf->indWy++;
  buf->indWy %= ROZMIAR;

  return wynik;
}

void* funProd(void *arg)
{
  struct bufor *myBufor = (struct bufor*) arg;
  int i;
  for (i=0; i<20; i++)
  {
    sleep(1);
    zapisz(myBufor, i);
  }
}

void* funKons(void *arg)
{
  struct bufor *myBufor = (struct bufor*) arg;
  int i;
  for (i=0; i<20; i++)
  {
    int x = odczyt(myBufor);
    printf("X=%d\n", x);
  }
}

