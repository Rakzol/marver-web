import random

balance = 0
balance_n = 0

lows = 0
wins = 0
tiros = 0
tiros_totales = 0

while True:

    tiros_totales += 1
    tiros += 1

    if random.randint(1,6) < 4:
        lows += 1
        if lows == 3:

<<<<<<< HEAD
            balance += (tiros-lows) * 50000 - 628125
=======
            balance += (tiros-lows) * 40000 - 415625
>>>>>>> 2784eea4da2f48135f95e154bafc9df6969287ad

            if balance < balance_n:
                balance_n = balance
                print(tiros_totales, ' = ', balance_n)
            else:
                print(tiros_totales, ' = ', 'wins: ', wins, ' lows: ', lows, 'tiros:', tiros-lows, 'balance: ', balance)

            if balance >= 10000000:
                exit()

            lows = 0
            wins = 0
            tiros = 0
    else:
        if lows == 0:
            wins += 1
        lows = 0