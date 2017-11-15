#include<stdio.h>
#include<pthread.h>
#include<stdlib.h>
#include<unistd.h>
#include<sys/wait.h>

void* funProd(void *arg);
void* funPosr(void *arg);
void* funKons(void *arg);

#define ROZM 16

struct bufCykl
{
  int dane[ROZM];
  int we;
  int wy;
  int licznik;
  pthread_mutex_t *semafor;  
  pthread_cond_t  *cond;
};

int odczyt(struct bufCykl *buf);
void zapis(struct bufCykl *buf, int wartosc);


struct bufCykl buforProdPosr;
struct bufCykl buforPosrKons;

int main()
{
  pthread_mutex_t semProdPosr  = PTHREAD_MUTEX_INITIALIZER;
  pthread_mutex_t semPosrKons  = PTHREAD_MUTEX_INITIALIZER;
  pthread_cond_t  condProdPosr = PTHREAD_COND_INITIALIZER;
  pthread_cond_t  condPosrKons = PTHREAD_COND_INITIALIZER;

  pthread_t producent;
  pthread_t posrednik;
  pthread_t konsument;

  buforProdPosr.we = buforProdPosr.wy = buforProdPosr.licznik = 0;
  buforProdPosr.semafor = &semProdPosr;
  buforProdPosr.cond    = &condProdPosr;
  buforPosrKons.we = buforPosrKons.wy = buforPosrKons.licznik = 0;
  buforPosrKons.semafor = &semPosrKons;
  buforPosrKons.cond    = &condPosrKons;

  pthread_create(&producent, NULL, funProd, &buforProdPosr);
  pthread_create(&posrednik, NULL, funPosr, NULL);
  pthread_create(&konsument, NULL, funKons, &buforPosrKons);

  pthread_join(producent, NULL);
  pthread_join(posrednik, NULL);
  pthread_join(konsument, NULL);
  return 0;
}

void* funProd(void *arg)
{
  int i;

  struct bufCykl *buf = (struct bufCykl *) arg;
  for (i=0; i<20; i++)
  {
    zapis(buf, i);
    sleep(1);
  }
}

void* funPosr(void *arg)
{
  int i;
  struct bufCykl *buf1 = &buforProdPosr;
  struct bufCykl *buf2 = &buforPosrKons;

  for (i=0; i<20; i++)
  {
    int x = odczyt(buf1);
    x*=2;
    zapis(buf2, x);
  }
}


void* funKons(void *arg)
{
  int i;
  struct bufCykl *buf = (struct bufCykl *) arg;

  for (i=0; i<20; i++)
  {
    int x = odczyt(buf);
    printf("x=%d\n", x);
  }
}

int odczyt(struct bufCykl *buf)
{
  int wynik;
  int odczytano = 0;
  pthread_mutex_lock (buf->semafor);

  while (buf->licznik == 0)
    pthread_cond_wait(buf->cond, buf->semafor);

  wynik = buf->dane[buf->wy];
  buf->licznik--;
  buf->wy++;
  buf->wy %= ROZM;

  pthread_mutex_unlock (buf->semafor);
  pthread_cond_signal(buf->cond);
  return wynik;
}

void zapis(struct bufCykl *buf, int wartosc)
{
  pthread_mutex_lock (buf->semafor);

  while (buf->licznik == ROZM)
    pthread_cond_wait(buf->cond, buf->semafor);
  
  buf->dane[buf->we] = wartosc;
  buf->licznik++;
  buf->we++;
  buf->we %= ROZM;

  pthread_mutex_unlock (buf->semafor);
  pthread_cond_signal(buf->cond);
}

