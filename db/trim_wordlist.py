f = open("english_word_list.txt")
for line in f:
	if not "'" in line:
		print(line.strip().lower())
