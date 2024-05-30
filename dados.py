import random

lows = 0
wins = 0
tiros = 0

while True:

    tiros += 1

    if random.randint(1,6) > 3:
        lows += 1
        if lows == 20:
            print('wins: ', wins, ' lows: ', lows, 'tiros:', tiros)
            exit()
    else:
        if lows == 0:
            wins += 1
        lows = 0

    print('wins: ', wins, ' lows: ', lows, 'tiros:', tiros)