// Require the packages we will use:
var http = require("http"),
    socketio = require("socket.io"),
    fs = require("fs"),
    users = [],
    roomsPubList = ["lobby"],
    roomsPvtList = ["secretlobby"],
    roomsPub = [ {Name: "lobby",
	              Topic: "Welcome to the lobby! Try /help or /h to get started.",
	              Mods: [],
	              Banlist: [],
	              Users: []} ], 
  roomsPvt = [ 	 {Name: "secretlobby",
		          Topic: "Welcome to the secret lobby! Try /help or /h to get started.",
		          Pass: "secret",
		          Mods: [],
		          Banlist: [],
		          Users: []} ];
		              
// Listen for HTTP connections.  This is essentially a miniature static file server that only serves our one file, client.html:
var app = http.createServer(function(req, resp){
    // This callback runs when a new connection is made to our HTTP server.
 
    fs.readFile("./client.html", function(err, data){
        // This callback runs when the client.html file has been read from the filesystem.
 
        if(err) return resp.writeHead(500);
        resp.writeHead(200);
        resp.end(data);
    });
});
app.listen(3458);

var io = socketio.listen(app);
io.sockets.on("connection", function(socket){
    socket.on('message_to_server', function(data) {
        // This callback runs when the server receives a new message from the client.
        var msg = data['message'];
        var usr = data['user'];
        var room = data['room'];
        var token = data['token'];
        var login = data['login'];
        
        console.log("user: "+usr+"; token: "+token+"room: "+room+"; message: "+msg); // log it to the Node.JS output
        if(msg.charAt(0) == "/") {
            splitmsg = msg.split(" ");
            command = splitmsg[0];
            console.log("user "+usr+" has called function "+command);
            switch(command) {
            	case "/pwd":
            		broadcastPvtSys("You are currently chatting in: "+room+" as "+usr+".", usr, token);
            		break;
            	case "/n":
                case "/name":
                	if(splitmsg.length < 2) {
                		broadcastPvtSys("Error: [/n]ame must be called as [/n]ame <new name>.", usr, token);
                	} else {
		                var newname = splitmsg[1];
		                console.log("user "+usr+" has requested name "+newname+".");
		                var j = users.indexOf(newname);
		                if (j != -1) {
		                	//name taken
		                	console.log("username "+newname+" is taken. Attempting to concat _");
		                	var newnewname = newname;
				            while (j != -1) {
				            	newname = newnewname;
				            	newnewname += "_";
				            	broadcastPvtSys("I could not change your name to "+newname+", as someone else has taken that name. I am attempting to change your name to "+newnewname+".", usr, token);
				            	j = users.indexOf(newnewname);
				            }
				            newname = newnewname;
				            console.log(usr+" has been changed to "+newname);
				            users.splice(j,1);
				            users.push(newnewname);
				            
				        } else {
		                	// name not registered
				            var i = users.indexOf(usr);
				            if(i == -1) {
				                users.push(newname);
				                console.log(newname+" has joined in room "+room);
				            } else {
				                users.splice(i,1);
				                users.push(newname);
				                console.log(usr+" has been changed to "+newname);
				                io.sockets.emit("change_name", {user:newname, oldname:usr, room:room, token:token, isNew:login});
				            }
				        }
				        
				        //give mods mod priv
				        var thisroom;
		       			for(var i = 0; i < roomsPub.length; i++) {
		                	thisroom = roomsPub[i];
				            if(thisroom.Mods.indexOf(usr) != -1) {
				        		if(thisroom.Mods.indexOf(newname) == -1) {
				        			var oldInd = thisroom.Mods.indexOf(usr);
				        			thisroom.Mods.splice(oldInd,1);
				        			thisroom.Mods.push(newname);
				        		}
			        		}
			        	}
			            for(var i = 0; i < roomsPvt.length; i++) {
		            		thisroom = roomsPvt[i];
				            if(thisroom.Mods.indexOf(usr) != -1) {
				        		if(thisroom.Mods.indexOf(newname) == -1) {
				        			var oldInd = thisroom.Mods.indexOf(usr);
				        			thisroom.Mods.splice(oldInd,1);
				        			thisroom.Mods.push(newname);
				        		}
			        		}
			        	}			        
					    if(login) {
				            io.sockets.emit("new_user", {user:newname, room:room, token:token});
					    } else {
					    	io.sockets.emit("change_name", {user:newname, oldname:usr, room:room, token:token, isNew:login});
					    }
					}
			        
                    break;
                case "/j":
                case "/join":
                	if(splitmsg.length < 2) {
                		broadcastPvtSys("Error: [/j]oin must be called as [/j]oin <room> <password>. <password> is only required if the room is password protected.", usr, token);
                	} else {
                		for (var i = 0; i < roomsPub.length; i++) {
                			var pubRoomUsers = roomsPub[i].Users;
                			var usrInd = pubRoomUsers.indexOf(usr);
                			if(usrInd != -1) {
                				pubRoomUsers.splice(usrInd, 1);
                			}
                		}
                		for (var i = 0; i < roomsPvt.length; i++) {
                			var pvtRoomUsers = roomsPvt[i].Users;
                			var usrInd = pvtRoomUsers.indexOf(usr);
                			if(usrInd != -1) {
                				pvtRoomUsers.splice(usrInd, 1);
                			}                		
                		}
		                var roomj = splitmsg[1];
		                var joinRoom;
		                var topic;
		                console.log(usr+" attempting to join "+roomj);
		                if(splitmsg.length >= 3) {
		                    console.log("private room "+roomj);
		                    var pass = splitmsg[2];
		       				if (roomsPubList.indexOf(roomj) != -1) {
		            			// the user is trying to join/create a private room, but a public room with the same name already exists
		            			var msg = "";
		            				msg += roomj+" is already a public room."
		            			roomJoinFail(usr, roomj, msg, token);
		        			} else if(roomsPvtList.indexOf(roomj) != -1) {
		                        console.log("room "+room+" exists");
		                        //room exists
		                        for(var i = 0; i < roomsPvt.length; i++) {
		                            if(roomsPvt[i].Name == roomj) { 
		                            joinRoom = roomsPvt[i];
		                            topic = roomsPvt[i].Topic;
		                            break;
		                            }
		                        }
		                        console.log(joinRoom.Pass);
		                        console.log(pass);
		                        if(joinRoom.Pass == pass){
		                            console.log("correct pass for private room "+roomj);
		                            //correct password
		                            if(joinRoom.Banlist.indexOf(usr) != -1) {
		                            	var msg = "You are banned from "+roomj+".";
		                                roomJoinFail(usr, roomj, msg, token);
		                            } else if(joinRoom.Users.indexOf(usr) == -1) {
		                                joinRoom.Users.push(usr);
		                                console.log(usr+" has joined private room "+roomj);
		                                roomJoined(usr, token, roomj, topic);
		                            } else {
		                                console.log(usr+" has joined private room "+roomj);
		                                roomJoined(usr, roomj, token);
		                            }
		                        } else {
		                            //incorrect password
		                            var msg = "Incorrect password to "+roomj+".";
		                            roomJoinFail(usr, roomj, msg, token);
		                        }
		                    } else {
		                        //room dne
		                        //<Name>, <Pass>, <Topic>, <Mods>, <Banlist>
		                        console.log("new private room "+roomj);
		                        var newChatRoom = { Name: roomj,
		                                            Topic:  "Set me with [/t]opic : <topic>. The ' : ' is necessary! Type /help for a list of commands.",
		                                            Pass: pass,
		                                            Mods: [usr],
		                                            Banlist: [],
		                                            Users: [usr] };
		                        newRoom(usr, roomj, token);
		                        roomJoined(usr, token, roomj, newChatRoom.Topic);
		                        roomsPvt.push(newChatRoom);
		                        roomsPvtList.push(roomj);
		                    };
		                } else {
		                	console.log("public room "+roomj);
		                    if (roomsPvtList.indexOf(roomj) != -1) {
		            // the user is trying to join/create a public room, but a private room with the same name already exists
		            		var msg = "";
		            				msg += roomj+" is already a private room.";
		            		roomJoinFail(usr, roomj, msg, token);
		       			} else if(roomsPubList.indexOf(roomj) != -1) {
		                        console.log("room "+roomj+" exists");
		                        //room exists
		                        for(var i = 0; i < roomsPub.length; i++) {
		                            if(roomsPub[i].Name == roomj) { 
		                                joinRoom = roomsPub[i];
		                                topic = roomsPub[i].Topic;
		                            break;
		                            }
		                        }
		                        if(joinRoom.Banlist.indexOf(usr) != -1) {
		                        	var msg = "you are banned.";
		                            roomJoinFail(usr, roomj, msg, token);
		                        } else if(joinRoom.Users.indexOf(usr) == -1) {
		                            joinRoom.Users.push(usr);
		                            console.log(usr+" has joined public room "+roomj);
		                            roomJoined(usr, token, roomj, topic);
		                        } else {
		                            console.log(usr+" has joined public room "+roomj);
		                            roomJoined(usr, token, roomj, topic);
		                        }
		                    } else {
		                        console.log("new public room "+roomj);
		                        //room dne
		                        //<Name>, <Topic>, <Mods>, <Banlist>
		                        var newChatRoom = { Name: roomj,
		                                            Topic: "Set me with [/t]opic : <topic>. The ' : ' is necessary! Type /help for a list of commands.",
		                                            Mods: [usr],
		                                            Banlist: [],
		                                            Users: [usr]} ;
		                        newRoom(usr, roomj, token);
		                        console.log(newChatRoom.Topic)
		                        roomJoined(usr, token, roomj, newChatRoom.Topic);
		                        roomsPub.push(newChatRoom);
		                        roomsPubList.push(roomj);
		                    }
		                }
				    }
                    break;
                case "/msg":
                	if(splitmsg.length < 2) {
                		broadcastPvtSys("Error: /msg must be called as /msg <user> : <msg>. The [ : ] is required.", usr, token);
                	} else {
		                var target = splitmsg[1];
		                console.log(target+" has been sent a pm");
		                if(users.indexOf(target) != -1) {
				            var pvtmsgList = msg.split(" : ");
				            if(pvtmsgList.length < 2) { 
				            	broadcastPvtSys("Error: /msg must be called as /msg <user> : <msg>. The [ : ] is required.", usr, token);
				            } else { 
				            	broadcastPvt(pvtmsgList[1], usr, target); 
				            }
		                } else {
		                	broadcastPvtSys("User does not exist.", usr, token);
		                }
		            }
                    break;
            	case "/u":
                case "/whois":
                    /*List the users in the room*/
                    var usrList = "";
                    var thisroom;
                    if(roomsPubList.indexOf(room)!=-1) {
	           			for(var i = 0; i < roomsPub.length; i++) {
	                    	if(roomsPub[i].Name == room) { 
	                    		thisroom = roomsPub[i];
	                    		break;
	                    	}
	                    }
                        for(var i = 0; i < thisroom.Users.length; i++){
                            usrList += thisroom.Users[i];
                            if(i + 1 < thisroom.Users.length) { usrList += ", "; }
                        }
                        listUsers(usr, room, usrList, token);
	                } else if (roomsPvtList.indexOf(room)!=-1) {
	           			for(var i = 0; i < roomsPvt.length; i++) {
	                    	if(roomsPvt[i].Name == room) { 
	                    		thisroom = roomsPvt[i];
	                    		break;
	                    	}
	                    }
                        for(var i = 0; i < thisroom.Users.length; i++){
                            usrList += thisroom.Users[i];
                            if(i + 1 < thisroom.Users.length) { usrList += ", "; }
                        }
                        listUsers(usr, room, usrList, token);
	                } else {
	                	broadcastPvtSys("You must in a chat room to use "+command+".", usr, token);
                	}
                    break;
                case "/mods":
                	/*List the moderators in your current room*/
                    var modList = "";
                    var thisroom;
                    if(roomsPubList.indexOf(room)!=-1) {
	           			for(var i = 0; i < roomsPub.length; i++) {
	                    	if(roomsPub[i].Name == room) { 
	                    		thisroom = roomsPub[i];
	                    		break;
	                    	}
	                    }
                        for(var i = 0; i < thisroom.Mods.length; i++){
                            modList += thisroom.Mods[i];
                            if(i < thisroom.Mods.length) { modList += ", "; }
                        }
                        listMods(usr, room, modList, token);
	                } else if (roomsPvtList.indexOf(room)!=-1) {
	           			for(var i = 0; i < roomsPvt.length; i++) {
	                    	if(roomsPvt[i].Name == room) { 
	                    		thisroom = roomsPvt[i];
	                    		break;
	                    	}
	                    }
                        for(var i = 0; i < thisroom.Mods.length; i++){
                            modList += thisroom.Mods[i];
                            if(i < thisroom.Mods.length) { modList += ", "; }
                        }
                        listMods(usr, room, modList, token);
	                } else {
	                	broadcastPvtSys("You must in a chat room to use "+command+".", usr, token);
                	}
                	break;
                case "/bans":
                	/*List the banees in your current room*/
                    var banList = "";
                    var thisroom;
                    if(roomsPubList.indexOf(room)!=-1) {
	           			for(var i = 0; i < roomsPub.length; i++) {
	                    	if(roomsPub[i].Name == room) { 
	                    		thisroom = roomsPub[i];
	                    		break;
	                    	}
	                    }
	                    if(thisroom.Banlist.length == 0) {
	                    	broadcastPvtSys("No one is banned in "+room+".", usr, token);
	                    } else {
		                    for(var i = 0; i < thisroom.Banlist.length; i++){
		                        banList += thisroom.Banlist[i];
		                        if(i + 2 < thisroom.Banlist.length) { banList += ", "; }
		                    }
		                    listBans(usr, room, banList, token);
		                }
	                } else if (roomsPvtList.indexOf(room)!=-1) {
	           			for(var i = 0; i < roomsPvt.length; i++) {
	                    	if(roomsPvt[i].Name == room) { 
	                    		thisroom = roomsPvt[i];
	                    		break;
	                    	}
	                    }
	                    if(thisroom.Banlist.length == 0) {
	                    	broadcastPvtSys("No one is banned in "+room+".", usr, token);
	                    } else {
		                    for(var i = 0; i < thisroom.Banlist.length; i++){
		                        banList += thisroom.Banlist[i];
		                        if(i + 1 < thisroom.Banlist.length) { banList += ", " }
		                    }
		                    listBans(usr, room, banList, token);
		                }
	                } else {
	                	broadcastPvtSys("You must in a chat room to use "+command+".", usr, token);
                	}
                	break;
                case "/ls":
                case "/rooms":
                	var roomList = "Public Rooms: ";
                	for (var i = 0; i < roomsPubList.length; i++) {
                	    console.log(roomsPubList[i]);
                		roomList += roomsPubList[i];
                		if(i + 1 < roomsPubList.length) { roomList += ", " } else { roomList += " " }
                	}
                	roomList += "\\n \
                				Private Rooms: ";
                	for (var i = 0; i < roomsPvtList.length; i++) {
                		console.log(roomsPvtList[i]);
                		roomList += roomsPvtList[i];
                		if(i + 1 < roomsPvtList.length) { roomList += ", " } else { roomList += " " }
                	}
                	listRooms(usr, roomList, token);
                	break;
                //mod-only
                case "/t":
                case "/topic":
               		var topicList = msg.split(" : ");
               		if (topicList.length < 2) {
               			broadcastPvtSys("Incorrect command. Try [/t]opic : <topic>. The colon [:] is required.", usr, token);
               		} else {
               			var thisroom;
               			if(roomsPubList.indexOf(room)!=-1) {
		           			for(var i = 0; i < roomsPub.length; i++) {
		                    	if(roomsPub[i].Name == room) { 
		                    		thisroom = roomsPub[i];
		                    		break;
		                    	}
		                    }
		                    if(thisroom.Mods.indexOf(usr) != -1) {
		                		thisroom.Topic = topicList[1]; 
		                		setTopic(room, topicList[1]);
	                		} else {
	                			broadcastPvtSys("You must be a moderator of the chat room to use "+command+".", usr, token);
	                		}
		                } else if (roomsPvtList.indexOf(room)!=-1) {
		           			for(var i = 0; i < roomsPvt.length; i++) {
		                    	if(roomsPvt[i].Name == room) { 
		                    		thisroom = roomsPvt[i];
		                    		break;
		                    	}
		                    }
		                    if(thisroom.Mods.indexOf(usr) != -1) {
		                		thisroom.Topic = topicList[1]; 
	                			setTopic(room, topicList[1]);
	                		} else {
	                			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr, token);
	                		}
		                } else {
	                		broadcastPvtSys("You must in a chat room and its moderator to use "+command, usr, token);
                		}
	                }
                    break;
                case "/mod":
                	if(splitmsg.length < 2) {
                		broadcastPvtSys("Incorrect command. Try /mod <user>.", usr, token);
                	} else {
		            	var newmod = splitmsg[1];
		            	var thisroom;
		       			if(roomsPubList.indexOf(room)!=-1) {
			       			for(var i = 0; i < roomsPub.length; i++) {
			                	if(roomsPub[i].Name == room) { 
			                		thisroom = roomsPub[i];
			                		break;
			                	}
			                }
			                if(thisroom.Mods.indexOf(usr) != -1) {
			            		if(thisroom.Mods.indexOf(newmod) != -1) {
			            			broadcastPvtSys("User "+newmod+" is already a moderator in room "+room+".", usr, token);
			            		} else {
			            			thisroom.Mods.push(newmod);
			            			mod(newmod, room);
			            		}
		            		} else {
		            			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr, token);
		            		}
			            } else if (roomsPvtList.indexOf(room)!=-1) {
			       			for(var i = 0; i < roomsPvt.length; i++) {
			                	if(roomsPvt[i].Name == room) { 
			                		thisroom = roomsPvt[i];
			                		break;
			                	}
			                }
			                if(thisroom.Mods.indexOf(usr) != -1) {
			            		if(thisroom.Mods.indexOf(newmod) != -1) {
			            			broadcastPvtSys("User "+newmod+" is already a moderator in room "+room+".", usr, token);
			            		} else {
			            			thisroom.Mods.push(newmod);
			            			mod(newmod, room);
			            		}
		            		} else {
		            			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr, token);
		            		}
			            } else {
			            	broadcastPvtSys("You must in a chat room and its moderator to use "+command, usr, token);
		            	}
		            }
                    break;
                case "/b":
                case "/ban":
                	if(splitmsg.length < 2) {
                		broadcastPvtSys("Incorrect command. Try /ban <user>.", usr, token);
                	} else {
		            	var banee = splitmsg[1];
		            	var thisroom;
		            	if(roomsPubList.indexOf(room) != -1) {
			       			for(var i = 0; i < roomsPub.length; i++) {
			                	if(roomsPub[i].Name == room) { 
			                		thisroom = roomsPub[i];
			                		break;
			                	}
			                }
			                if(thisroom.Mods.indexOf(usr) != -1) {
			                	var ind = thisroom.Users.indexOf(banee);
				            	if(ind == -1) {
				        			broadcastPvtSys("User "+banee+" is not in "+room+".", usr, token);
				        		} else if(thisroom.Banlist.indexOf(banee) != -1) {
			            			broadcastPvtSys("User "+banee+" is already banned in "+room+".", usr, token);
				            	} else {
				        			thisroom.Banlist.push(banee);
				        			thisroom.Users.splice(ind, 1);
				        			broadcastPvtSys("User "+banee+" has been banned from "+room+".", usr, token);
					                var reasonList =  msg.split(" : ");
								    var reason;
								    if(reasonList.length < 2) { 
								    	broadcastPvtSys("User "+banee+" will not be given a reason for their ban/kick.", usr, token);
								    	reason = "None given";
									} else { 
										reason = reasonList[1];
								   	}
								   	kick(room, banee, reason);
				        		}
		            		} else {
		            			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr, token);
		            		}
			            } else if (roomsPvtList.indexOf(room) != -1) {
			       			for(var i = 0; i < roomsPvt.length; i++) {
			                	if(roomsPvt[i].Name == room) { 
			                		thisroom = roomsPvt[i];
			                		break;
			                	}
			                }
			                if(thisroom.Mods.indexOf(usr) != -1) {
			                	var ind = thisroom.Users.indexOf(banee);
				            	if(ind == -1) {
				        			broadcastPvtSys("User "+banee+" is not in "+room+".", usr, token);
				        		} else if(thisroom.Banlist.indexOf(banee) != -1) {
			            			broadcastPvtSys("User "+banee+" is already banned in "+room+".", usr, token);
				            	} else {
				        			thisroom.Banlist.push(banee);
				        			thisroom.Users.splice(ind, 1);
				        			broadcastPvtSys("User "+banee+" has been banned in "+room+".", usr, token);
					                var reasonList =  msg.split(" : ");
								    var reason;
								    if(reasonList.length < 2) { 
								    	broadcastPvtSys("User "+banee+" will not be given a reason for their ban/kick.", usr, token);
								    	reason = "None given";
									} else { 
										reason = reasonList[1];
								   	}
								   	kick(room, banee, reason);
				        		}
		            		} else {
		            			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr, token);
		            		}
			            } else {
			            	broadcastPvtSys("You must in a chat room and its moderator to use "+command, usr, token);
		            	}
	            	}
	            	break;
		        case "/k":
                case "/kick":
                	if(splitmsg.length < 2) {
                		broadcastPvtSys("Incorrect command. Try /kick <user>.", usr, token);
                	} else {
		            	var banee = splitmsg[1];
		            	var thisroom;
		            	if(roomsPubList.indexOf(room)!=-1) {
			       			for(var i = 0; i < roomsPub.length; i++) {
			                	if(roomsPub[i].Name == room) { 
			                		thisroom = roomsPub[i];
			                		break;
			                	}
			                }
			                if(thisroom.Mods.indexOf(usr) != -1) {
			                	var ind = thisroom.Users.indexOf(banee);
			                	if(ind == -1) {
			            			broadcastPvtSys("User "+banee+" is not in "+room+".", usr, token);
			            		} else {
			            			thisroom.Users.splice(ind, 1);
			            			broadcastPvtSys("User "+banee+" is has been kicked from "+room+".", usr, token);
					                var reasonList =  msg.split(" : ");
								    var reason;
								    if(reasonList.length < 2) { 
								    	broadcastPvtSys("User "+banee+" will not be given a reason for their ban/kick.", usr, token);
								    	reason = "None given";
									} else { 
										reason = reasonList[1];
								   	}
								   	kick(room, banee, reason);
			            		}
		            		} else {
		            			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr, token);
		            		}
			            } else if (roomsPvtList.indexOf(room)!=-1) {
			       			for(var i = 0; i < roomsPvt.length; i++) {
			                	if(roomsPvt[i].Name == room) { 
			                		thisroom = roomsPvt[i];
			                		break;
			                	}
			                }
			                if(thisroom.Mods.indexOf(usr) != -1) {
			                	var ind = thisroom.Users.indexOf(banee);
			                	if(ind == -1) {
			            			broadcastPvtSys("User "+banee+" is not in "+room+".", usr, token);
			            		} else {
			            			thisroom.Users.splice(ind, 1);
			            			broadcastPvtSys("User "+banee+" is has been kicked from "+room+".", usr, token);
					                var reasonList =  msg.split(" : ");
								    var reason;
								    if(reasonList.length < 2) { 
								    	broadcastPvtSys("User "+banee+" will not be given a reason for their ban/kick.", usr, token);
								    	reason = "None given";
									} else { 
										reason = reasonList[1];
								   	}
								   	kick(room, banee, reason);
			            		}
		            		} else {
		            			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr);
		            		}
			            } else {
			            	broadcastPvtSys("You must in a chat room and its moderator to use "+command, usr, token);
		            	}
		            	

		            }
                    break;
                case "/un":
                case "/unban":
                	if(splitmsg.length < 2) {
                		broadcastPvtSys("Incorrect command. Try /unban <user>.", usr, token);
                	} else {
		            	var unbanee = splitmsg[1];
		            	var thisroom;
		            	if(roomsPubList.indexOf(room)!=-1) {
			       			for(var i = 0; i < roomsPub.length; i++) {
			                	if(roomsPub[i].Name == room) { 
			                		thisroom = roomsPub[i];
			                		break;
			                	}
			                }
			                if(thisroom.Mods.indexOf(usr) != -1) {
			                	var ind = thisroom.Banlist.indexOf(unbanee);
			                	if(ind == -1) {
			            			broadcastPvtSys("User "+unbanee+" is not banned in "+room+".", usr, token);
			            		} else {
			            			thisroom.Banlist.splice(ind, 1);
			            			unban(unbanee, room);
			            		}
		            		} else {
		            			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr, token);
		            		}
			            } else if (roomsPvtList.indexOf(room)!=-1) {
			       			for(var i = 0; i < roomsPvt.length; i++) {
			                	if(roomsPvt[i].Name == room) { 
			                		thisroom = roomsPvt[i];
			                		break;
			                	}
			                }
			                if(thisroom.Mods.indexOf(usr) != -1) {
			                	var ind = thisroom.Banlist.indexOf(unbanee);
			                	if(ind == -1) {
			            			broadcastPvtSys("User "+unbanee+" is not banned in "+room+".", usr, token);
			            		} else {
			            			thisroom.Banlist.splice(ind, 1);
			            			unban(unbanee, room);
			            		}
		            		} else {
		            			broadcastPvtSys("You must be a moderator of the chat room to use "+command, usr, token);
		            		}
			            } else {
			            	broadcastPvtSys("You must in a chat room and its moderator to use "+command, usr, token);
		            	}
		            }
		            break;
		        case "/h":
                case "/help":
                	var thisroom;
                	var output = "Commands (Shortcuts in []): \\n \
										Get help: [/h]elp \\n \
										Get your current name and room: /pwd \\n \
										Join a room: [/j]oin <room> \\n \
										Join a private room: [/j]oin <room> <password> \\n \
										List people in a room: /whois [/u] \\n \
										List all moderators in a room: /mods \\n \
										List all banees in a room: /bans \\n \
										List all rooms: /rooms [/ls] \\n \
										Change your name: [/n]ame <new name> \\n \
										Private message another user: /msg <user> : <message> ";
                	if(roomsPubList.indexOf(room)!=-1) {
	           			for(var i = 0; i < roomsPub.length; i++) {
	                    	if(roomsPub[i].Name == room) { 
	                    		thisroom = roomsPub[i];
	                    		break;
	                    	}
	                    }
	                    if(thisroom.Mods.indexOf(usr) != -1) {
	                    	output += "\\n \
	                    			   Kick a user: [/k]ick <user> <reason> \\n \
									   Ban a user: [/b]an <user> <reason> \\n \
									   Unban a user: [/un]ban <user> \\n \
									   Add a new moderator: /mod <user> \\n \
									   Set the topic: [/t]opic : <topic>";
                		}
	                } else if (roomsPvtList.indexOf(room)!=-1) {
	           			for(var i = 0; i < roomsPvt.length; i++) {
	                    	if(roomsPvt[i].Name == room) { 
	                    		thisroom = roomsPvt[i];
	                    		break;
	                    	}
	                    }
	                    if(thisroom.Mods.indexOf(usr) != -1) {
	                    	output += "\\n \
	                    			   Kick a user: [/k]ick <user> <reason> \\n \
									   Ban a user: [/b]an <user> <reason> \\n \
									   Unban a user: [/un]ban <user> \\n \
									   Add a new moderator: /mod <user> \\n \
									   Set the topic: [/t]opic : <topic>";
                		} 
	                }
	                broadcastPvtSys(output, usr, token);
	                break;
                default:
                    var output = "Unknown command: "+command+".";
                    broadcastPvtSys(output, usr, token);
            };
        } else {
            broadcastAll(msg, usr, room);
        }
    });
    
    function alert(message, token) {
    	io.sockets.emit("alert", {message:message, token:token});
    }
    
    function listUsers(user, room, users, token) {
        io.sockets.emit("list_users", {user:user, room:room, users:users, token:token});
    }
    
    function listMods(user, room, mods, token) {
        io.sockets.emit("list_mods", {user:user, room:room, mods:mods, token:token});
    }
    
    function listBans(user, room, bans, token) {
        io.sockets.emit("list_bans", {user:user, room:room, bans:bans, token:token});
    }
    
    function listRooms(user, rooms, token) {
    	io.sockets.emit("list_rooms", {user:user, rooms:rooms, token:token});
    }
    
    function roomJoined(user, token, room, topic) {
        io.sockets.emit("room_joined", {user:user, room:room, topic:topic, token:token});
    }
    
    function newRoom(user, room, token) {
        io.sockets.emit("new_room", {user:user, room:room, token:token});
    }
    
    function roomJoinFail(user, room, msg, token) {
        io.sockets.emit("join_fail", {user:user, room:room, msg:msg, token:token});
    }
    
    function setTopic(room, topic) {
        io.sockets.emit("topic", {room:room, topic:topic});
    }
    
    function kick(room, user, reason) {
        io.sockets.emit("kick", {room:room, user:user, reason:reason});
    }
    
    function unban(user, room) {
    	io.sockets.emit("unban", {user:user, room:room});
    }
    
    function mod(user, room) {
    	io.sockets.emit("mod", {user:user, room:room});
    }
    
    function broadcastSys(msg) {
        io.sockets.emit("sys_msg", {message:msg});
    }
    
    function broadcastAll(msg, from, room) {
        io.sockets.emit("all_msg", {user:from, message:msg, room:room});
    }
    
    function broadcastPvt(msg, from, to) {
        io.sockets.emit("pvt_msg", {to:to, from:from, message:msg})
    }
    
    function broadcastPvtSys(msg, to, token) {
        io.sockets.emit("sys_pvt", {target:to, message:msg, token:token})
    }
    
    
    
});
