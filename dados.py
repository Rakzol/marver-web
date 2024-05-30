import random

lows = 0
wins = 0

while True:

    if random.randint(1,6) > 3:
        lows += 1
        if lows == 200:
            print('wins: ', wins, ' lows: ', lows)
            exit()
    else:
        if lows == 0:
            wins += 1
        lows = 0

    print('wins: ', wins, ' lows: ', lows)