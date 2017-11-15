#include<stdio.h>
#include<pthread.h>
#include<stdlib.h>
#include<unistd.h>
#include<string.h>
#include<sys/wait.h>

#define ROZM_BUF 16

void* funProd(void *arg);
void* funKons(void *arg);

struct bufor
{
  int dane[ROZM_BUF];
  int licznik;
  int we;
  int wy;
};

typedef struct bufor bufor_t;

int  czytaj(bufor_t *buf);
void zapisz(bufor_t *buf, int wartosc);
void inicjalizacjaBufora(bufor_t *buf);

int main()
{
  bufor_t bufGlob;
  inicjalizacjaBufora(&bufGlob);


  pthread_t producent;
  pthread_t konsument;


  pthread_create(&producent, NULL, funProd, &bufGlob);
  pthread_create(&konsument, NULL, funKons, &bufGlob);

  pthread_join(producent, NULL);
  pthread_join(konsument, NULL);
  return 0;
}

void* funProd(void *arg)
{
  bufor_t *buf = (bufor_t *) arg;
  int i;
  for (i=0; i<20; i++)
  {
    sleep(1);
    zapisz(buf, i);
  }
}

void* funKons(void *arg)
{
  bufor_t *buf = (bufor_t *) arg;
  int i;
  for (i=0; i<20; i++)
  {
    int x = czytaj(buf);
    printf("x = %d\n", x);
  }
}


int czytaj(bufor_t *buf)
{
  while (buf->licznik == 0)
    ;

  buf->licznik--;
  int wynik = buf->dane[buf->wy];
  buf->wy++;
  buf->wy %= ROZM_BUF;

  return wynik;
}

void zapisz(bufor_t *buf, int wartosc)
{
  //opuść semafor
  while (buf->licznik == ROZM_BUF)
    ;
  
  buf->licznik++;
  buf->dane[buf->we] = wartosc;
  buf->we++;
  buf->we %= ROZM_BUF;
  //podnieś semafor
}

void inicjalizacjaBufora(bufor_t *buf)
{
  memset(buf, 0, sizeof(struct bufor));
//  buf->we = 0;
//  buf->wy = 0;
//  buf->licznik = 0;
}


