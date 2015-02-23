#!/usr/bin/python
import numpy as np, re, ast, sys, os

if len(sys.argv) != 2:
	sys.exit("Check command line argument[s]")
 
filename = sys.argv[1]
 
if not os.path.exists(filename):
	sys.exit("Error: File '%s' not found" % sys.argv[1])

def get_stats(season):
	f = open(season, 'r')
	data = f.read()
	stat_regex = re.compile(r"([\w]+[\s][\w]+) batted ([0-9]) times with ([0-9]) hits")
	iterThruFile = stat_regex.finditer(data)
	stats = np.array([[0,0]])
	players = ['']
	for match in iterThruFile:
		player = match.group(1)
		at_bats = ast.literal_eval(match.group(2))
		hits = ast.literal_eval(match.group(3))

		if player not in players:
			players.append(player)
			new_line = [[at_bats, hits]]
			stats = np.concatenate((stats,new_line),axis=0)
		else:
			player_row = players.index(player)
			stats[player_row,0] += at_bats
			stats[player_row,1] += hits
	return players,stats

players,stats = get_stats(filename)

if len(players) != len(stats):
	print("Error! Check input and contact the author.")
	exit()

res = []
for i in range(1, len(players)):
	res.append((players[i],stats[i,1]/stats[i,0]))
	
def getKey(key): return key[1]
res = sorted(res, key=getKey, reverse=True)

for stmt in res:
	print("%s: %.3f" % (stmt))
	
