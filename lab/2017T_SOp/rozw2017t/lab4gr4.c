#include<stdio.h>
#include<pthread.h>
#include<stdlib.h>
#include<unistd.h>
#include<sys/wait.h>

void* funProd(void *arg);
void* funKons(void *arg);

#define ROZM 16

struct bufCykl
{
  int dane[ROZM];
  int we;
  int wy;
  int licznik;
};

int odczyt(struct bufCykl *buf);
void zapis(struct bufCykl *buf, int wartosc);

pthread_mutex_t semafor = PTHREAD_MUTEX_INITIALIZER;

int main()
{
  struct bufCykl mojBufor;

  pthread_t producent;
  pthread_t konsument;

  mojBufor.we = mojBufor.wy = mojBufor.licznik = 0;

  pthread_create(&producent, NULL, funProd, &mojBufor);
  pthread_create(&konsument, NULL, funKons, &mojBufor);

  pthread_join(producent, NULL);
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
  do
  {
    pthread_mutex_lock (&semafor);
    if (buf->licznik > 0)
    {
      wynik = buf->dane[buf->wy];
      buf->licznik--;
      buf->wy++;
      buf->wy %= ROZM;
      odczytano = 1;
    }
    pthread_mutex_unlock (&semafor);
    sleep(1);
  }
  while (odczytano == 0);
  return wynik;
}

void zapis(struct bufCykl *buf, int wartosc)
{
  int zapisano = 0;
  do
  {
    pthread_mutex_lock (&semafor);
    if (buf->licznik < ROZM)
    {
      buf->dane[buf->we] = wartosc;
      buf->licznik++;
      buf->we++;
      buf->we %= ROZM;
      zapisano = 1;
    }
    pthread_mutex_unlock (&semafor);
    sleep(1);
  }
  while(zapisano == 0);
}

