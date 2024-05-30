import random

balance = 0
lows = 0
wins = 0
tiros = 0

while True:

    tiros += 1

    if random.randint(1,6) > 3:
        lows += 1
        if lows == 8:

            balance += tiros * 50000 - 26233633

            print('wins: ', wins, ' lows: ', lows, 'tiros:', tiros, 'balance: ', balance)

            lows = 0
            wins = 0
            tiros = 0
    else:
        if lows == 0:
            wins += 1
        lows = 0