from PIL import ImageGrab

from time import sleep

sleep(5)

screenshot = ImageGrab.grab()

screenshot.show()

screenshot.save("screenshot2.png", format="png")