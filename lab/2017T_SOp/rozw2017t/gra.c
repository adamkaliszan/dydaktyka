#include <stdio.h>
#include <stdint.h>
#include <string.h>

#define BOT 1

typedef enum
{
  BRAK    = 0,
  KOLKO   = 1,
  KRZYZYK = 2
} ruch_t;

typedef enum
{
  G_KOLKO   = 0,
  G_KRZYZYK = 1
} gracz_t;

typedef enum
{
  WYGRYWA_KOLKO     = -1,
  REMIS             =  0,
  WYGRYWA_KRZYZYK   =  1,
  NIEROZSTRZYGNIETA =  2
} rezultat_t;

typedef struct
{
  uint8_t ruchy[9];
} plansza_t;

void czysc_plansze(plansza_t *plansza)
{
  memset(plansza, BRAK, sizeof(plansza_t));
}

char pokaz_pole(ruch_t pole)
{
  char rezultat;
  switch (pole)
  {
    case KOLKO:
      rezultat = 'O';
    break;

    case KRZYZYK:
      rezultat = 'X';
    break;

    default:
      rezultat = '-';
    break;
  }
  return rezultat;
}

void pokaz_plansze(plansza_t *plansza)
{
  for (int y=0; y<3; y++)
  {
    for (int x=0; x<3; x++)
    {
      printf("%c", pokaz_pole(plansza->ruchy[3*y+x]));
    }
    printf("\n"); 
  }
}

int zaznacz_ruch_n(plansza_t *plansza, gracz_t gracz, int nrPola)
{
  if (plansza->ruchy[nrPola] != BRAK)
    return 0;
  plansza->ruchy[nrPola] = (gracz == G_KOLKO) ? KOLKO : KRZYZYK;
  return 1;
}

int zaznacz_ruch(plansza_t *plansza, gracz_t gracz, int nrWiersza, int nrKolumny)
{
  nrWiersza--;
  nrKolumny--;
  int nrPola = 3*nrWiersza + nrKolumny;
  return zaznacz_ruch_n(plansza, gracz, nrPola);
}

int usun_ruch_n(plansza_t *plansza, int nrPola)
{
  if (plansza->ruchy[nrPola] == BRAK)
    return 0;
  plansza->ruchy[nrPola] = BRAK;
  return 1;

}

void zmien_gracza(gracz_t *gracz)
{
  *gracz = (*gracz == G_KOLKO) ? G_KRZYZYK : G_KOLKO;
}

rezultat_t rezultat_gry(plansza_t *plansza)
{
  //Sprawdzanie wierszy
  for (int y=0; y<3; y++)
  {
    if ((plansza->ruchy[3*y] == plansza->ruchy[3*y + 1]) && (plansza->ruchy[3*y] == plansza->ruchy[3*y + 2]))
    {
      if (plansza->ruchy[3*y] == KOLKO)
        return WYGRYWA_KOLKO;
      if (plansza->ruchy[3*y] == KRZYZYK)
        return WYGRYWA_KRZYZYK;
    }
  }
  //Sprawdzanie kolumn
  for (int x=0; x<3; x++)
  {
    if ((plansza->ruchy[x] == plansza->ruchy[x + 3]) && (plansza->ruchy[x] == plansza->ruchy[x + 6]))
    {
      if (plansza->ruchy[x] == KOLKO)
        return WYGRYWA_KOLKO;
      if (plansza->ruchy[x] == KRZYZYK)
        return WYGRYWA_KRZYZYK;
    }
  }
  //Sprawdzanie przekątnych
  if ((plansza->ruchy[0] == plansza->ruchy[4]) && (plansza->ruchy[0] == plansza->ruchy[8]))
  {
    if (plansza->ruchy[4] == KOLKO)
      return WYGRYWA_KOLKO;
    if (plansza->ruchy[4] == KRZYZYK)
      return WYGRYWA_KRZYZYK;
  }
  if ((plansza->ruchy[2] == plansza->ruchy[4]) && (plansza->ruchy[2] == plansza->ruchy[6]))
  {
    if (plansza->ruchy[4] == KOLKO)
      return WYGRYWA_KOLKO;
    if (plansza->ruchy[4] == KRZYZYK)
      return WYGRYWA_KRZYZYK;
  }
  //sprawdzanie możliwości wykonania ruchu

  for (int i=0; i<9; i++)
    if (plansza->ruchy[i] == BRAK)
      return NIEROZSTRZYGNIETA;

  return REMIS;
}

int ocen_rezultat(gracz_t gracz, rezultat_t rezultat)
{
  if (rezultat == REMIS) return 0;

  if (((gracz == G_KOLKO) && (rezultat == WYGRYWA_KOLKO)) || ((gracz == G_KRZYZYK) && (WYGRYWA_KRZYZYK)))
    return 1;

  return -1;
}

/**
 * @return -1 przegrana, 0 remis, 1 wygrana
 */
int wykonaj_ruch(plansza_t *plansza, gracz_t gracz, int *nrPola)
{
  int najlRuch = -1;
  int najlRuchPkt = -2;

  int tempWynik;
  for (int i=0; i<9; i++)
  {
    if (zaznacz_ruch_n(plansza, gracz, i) == 0) continue;
    
    rezultat_t tmpRez = rezultat_gry(plansza);
    if (tmpRez != NIEROZSTRZYGNIETA)
    {
      tempWynik = ocen_rezultat(gracz, tmpRez);
    }
    else
    {
      gracz_t przeciwnik = gracz;
      zmien_gracza(&przeciwnik);
      tempWynik = (-1) * wykonaj_ruch(plansza, przeciwnik, NULL);
    }
    if (tempWynik > najlRuchPkt)
    {
      najlRuchPkt = tempWynik;
      najlRuch = i;
    }
    usun_ruch_n(plansza, i);
  }

  if (nrPola != NULL)
  {
    *nrPola = najlRuch;
  }
  return najlRuchPkt;
}

int main()
{
  plansza_t mojaPlansza;
  gracz_t aktGracz = G_KOLKO;
  
  czysc_plansze(&mojaPlansza);
  while (rezultat_gry(&mojaPlansza) == NIEROZSTRZYGNIETA)
  {
    int nrWiersza;
    int nrKolumny;
    printf("Ruch wykonuje %s\n", (aktGracz == G_KOLKO) ? "Kółko" : "Krzyżyk");
    printf("\tpodaj nr wiersza (1-3): ");
    scanf("%d", &nrWiersza);
    printf("\tpodaj nr kolumny (1-3): ");
    scanf("%d", &nrKolumny);
    if (zaznacz_ruch(&mojaPlansza, aktGracz, nrWiersza, nrKolumny) == 0)
      continue;

    zmien_gracza(&aktGracz);
    pokaz_plansze(&mojaPlansza);

#ifdef BOT
    if (rezultat_gry(&mojaPlansza) != NIEROZSTRZYGNIETA)
      break;

    int n = -1;
    wykonaj_ruch(&mojaPlansza, aktGracz, &n);
    zaznacz_ruch_n(&mojaPlansza, aktGracz, n);
    zmien_gracza(&aktGracz);   
    pokaz_plansze(&mojaPlansza);
#endif /*BOT*/
  } 
  printf("Koniec gry, rezultat");
  //TODO dopisać odpowiedni komunikat
  printf("\n");

  return 0;
}

