import random

lows = 0
contador = 0

while True:

    if random.randint(1,6):
        lows += 1

        if lows == 10:
            print(contador)
            exit()
    
    contador += 1