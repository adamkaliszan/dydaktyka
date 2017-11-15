#include<stdio.h>
#include<pthread.h>
#include<stdlib.h>
#include<unistd.h>
#include<string.h>
#include<sys/wait.h>

#define ROZM_BUF 16

void* funProd(void *arg);
void* funPosr(void *arg);
void* funKons(void *arg);

struct bufor
{
  int dane[ROZM_BUF];
  int licznik;
  int we;
  int wy;
  pthread_cond_t *zmWar;
  pthread_mutex_t *semafor;
};

typedef struct bufor bufor_t;

int  czytaj(bufor_t *buf);
void zapisz(bufor_t *buf, int wartosc);
void inicjalizacjaBufora(bufor_t *buf, pthread_cond_t *zmWar, pthread_mutex_t *semafor);



int main()
{
  bufor_t bufProdPosr;
  bufor_t bufPosrKons;

  pthread_cond_t zmWarProdPosr    = PTHREAD_COND_INITIALIZER;
  pthread_mutex_t semaforProdPosr = PTHREAD_MUTEX_INITIALIZER;
  pthread_cond_t zmWarPosrKons    = PTHREAD_COND_INITIALIZER;
  pthread_mutex_t semaforPosrKons = PTHREAD_MUTEX_INITIALIZER;

  inicjalizacjaBufora(&bufProdPosr, &zmWarProdPosr, &semaforProdPosr);
  inicjalizacjaBufora(&bufPosrKons, &zmWarPosrKons, &semaforPosrKons);

  pthread_t producent;
  pthread_t posrednik;
  pthread_t konsument;

  bufor_t *tablica[] = {&bufProdPosr, &bufPosrKons};

  pthread_create(&producent, NULL, funProd, &bufProdPosr);
  pthread_create(&posrednik, NULL, funPosr, tablica);
  pthread_create(&konsument, NULL, funKons, &bufPosrKons);

  pthread_join(producent, NULL);
  pthread_join(posrednik, NULL);
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

void* funPosr(void *arg)
{
  bufor_t **buf = (bufor_t **) arg;

  bufor_t *buf1 = buf[0];
  bufor_t *buf2 = buf[1];

  int i;
  for (i=0; i<20; i++)
  {
    int x = czytaj(buf1);
    x *=2;
    zapisz(buf2, x);
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
  pthread_mutex_lock(buf->semafor);
  do
  {  
    if (buf->licznik > 0)
      break;

    pthread_cond_wait(buf->zmWar, buf->semafor);
  }
  while (1);

  buf->licznik--;
  int wynik = buf->dane[buf->wy];
  buf->wy++;
  buf->wy %= ROZM_BUF;
  pthread_mutex_unlock(buf->semafor);
  pthread_cond_signal (buf->zmWar);
  return wynik;
}

void zapisz(bufor_t *buf, int wartosc)
{
  pthread_mutex_lock(buf->semafor);

  for ( ; ; )
  {
    if (buf->licznik < ROZM_BUF)
      break;
    pthread_cond_wait(buf->zmWar, buf->semafor);
  }
  
  
  buf->licznik++;
  buf->dane[buf->we] = wartosc;
  buf->we++;
  buf->we %= ROZM_BUF;
  pthread_mutex_unlock(buf->semafor);
  pthread_cond_signal (buf->zmWar);
}

void inicjalizacjaBufora(bufor_t *buf, pthread_cond_t *zmWar, pthread_mutex_t *semafor)
{
  memset(buf, 0, sizeof(struct bufor));
  buf->zmWar = zmWar;
  buf->semafor = semafor;
//  buf->we = 0;
//  buf->wy = 0;
//  buf->licznik = 0;
}


