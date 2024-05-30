import random

balance = 0
balance_n = 0

lows = 0
wins = 0
tiros = 0

while True:

    tiros += 1

    if random.randint(1,6) < 4:
        lows += 1
        if lows == 3:

            balance += (tiros-lows) * 40000 - 415625

            if balance < balance_n:
                balance_n = balance
                print(balance_n)

            #print('wins: ', wins, ' lows: ', lows, 'tiros:', tiros, 'balance: ', balance)

            lows = 0
            wins = 0
            tiros = 0
    else:
        if lows == 0:
            wins += 1
        lows = 0