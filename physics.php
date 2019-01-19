<!DOCTYPE html>
<html style="height:100%">
    <head>
            <?php include("template/config.php") ?>
            <title>physics</title>
            <meta name="title" content ="2d physics demo">
            <meta name="description" content="A demo of a 2d elastic collision between balls of variable size and mass">
            <meta property="og:title" content="2d physics demo" />
            <meta property="og:description" content="A demo of a 2d elastic collision between balls of variable size and mass" />
            <meta property="og:image" content="<?= $domain ?>/projects/images/2DEC.png" />
    </head>
<body>
        <?php include("template/HaS.php");?>
        <div id="content">
            <div id="main-body">
    		  <canvas style="" id="myCanvas" style="border:1px solid #d3d3d3;" width="500px" height="500px"></canvas>
                <canvas style="" id="collisions" style="border:1px solid #d3d3d3;" width="500px" height="500px"></canvas>
            </div>
            <div id="controls">
                <div class="slidecontainer">
                    speed: <input type="range" min="10" max="1000" value="10" class="slider" id="myRange" oninput="">
                </div>
                <br>
                <div class="settings">
                    <h1>settings:</h1>
                    focus<input id="focus" type="checkBox" checked onchange="window.focus = this.checked; console.log(focus);" /><br>
                    drawVectors<input id="drawVectors" type="checkBox" onchange="window.drawVectors = this.checked;" /><br>
                    variableMass<input id="variableMass" checked type="checkBox" onchange="window.variableMass = this.checked;" /><br>
                    variableRadius<input id="variableRadius" type="checkBox" onchange="window.variableRadius = this.checked;" /><br><br>
    
                    maxMass <input type="text" value="100" onchange="maxMass = parseInt(this.value);" /><br>
                    maxRadius <input type="text" value="15" onchange="maxRadius = parseInt(this.value);" /><br>
                    ballCount <input type="text" value="10" onchange="ballCount = parseInt(this.value);" /><br>
                    zoomFocus <input type="text" value="3" onchange="zoomFocus = parseInt(this.value);" />
    
                </div>
    		
    	    <button onClick="resetBalls();">reset</button>
            </div>
            <script>
                var maxMass = 100;
                var maxRadius = 15;
                var drawVectors = false;
                var focus = true;
                var ballCount = 10;
                var variableMass = true;
                var variableRadius = false;
                var zoomFocus = 3;
    
                function ball(mass, radius){
                    this.x = Math.random()*490+5;
                    this.y = Math.random()*490+5;
    
                    this.vx = 0;
                    this.vy = 0;
                    this.vxms = 0;
                    this.vxms = 0;
    
                    this.canFlipX = true;
                    this.canFlipY = true;
    
                    this.stopped = false;
    
                    this.vScale = (13/500);
                    this.r = radius;
                    this.gx = 0;
                    this.gy = 0;
                    this.m = mass;
                    this.bouncyness = 0.9;
                    this.groundFriction = 0.9;
                    this.vectorDrawSize = 20;
                    this.canCollide = true;
    
                    this.checkStopped = function(){
                        if(Math.round(this.y) == (500 - this.r) && Math.round(this.vy*20) == 0){
                            this.stopped = true;
                        }
                    }
    
                    this.checkError = function(){
                        if(this.vyms > 0.6){
                            this.speedP(this.vx, 0);
                        }
                        if(this.vxms > 0.6){
                            this.speedP(0, this.vy);
                        }
                    }
    
                    this.convertSpeedToP = function(){
                        this.vx = this.vxms / this.vScale;
                        this.vy = this.vyms / this.vScale;
                    }
    
                    this.convertSpeedToMS = function(){
                        this.vxms = this.vx * this.vScale;
                        this.vyms = this.vy * this.vScale;
                    }
    
                    this.speed = function(x, y){
                        this.vxms = x;
                        this.vyms = y;
    
                        this.convertSpeedToP();
    
                        this.gx = this.m * this.vx;
                        this.gy = this.m * this.vy;
                    }
    
                    this.momentum = function(gx1, gy1){
                        this.gx = gx1;
                        this.gy = gy1;
    
                        this.vx = gx1 / this.m;
                        this.vy = gy1 / this.m;
    
                        this.convertSpeedToMS();
                    }
    
                    this.speedP = function(x, y){
                        this.vx = x;
                        this.vy = y;
    
                        this.convertSpeedToMS();
    
                        this.gx = this.m * this.vx;
                        this.gy = this.m * this.vy;
                        
                    }
    
                    this.move = function(){
                        this.x += this.vx;
    
                        this.checkError();
    
                        if(this.stopped){
                            this.vxyms = 0;
                            this.vy = 0;
                        }
    
                        if(!this.stopped){
                            this.y += this.vy;
                        }
                    }
    
                    this.draw = function(ctx, color = ""){
                        if( color == ""){
                            color = (255-parseInt((this.m/maxMass)*255)).toString(16);
                            if (color.length % 2) {
                              color = '0' + color;
                            }
    
                            color = "#" + color+color+color;
                        }
    
                        ctx.fillStyle = color;
                        ctx.beginPath();
                        ctx.arc(this.x, this.y, this.r, 0, 2 * Math.PI);
                        ctx.fill();
                        ctx.closePath();
                        ctx.stroke();
                        ctx.fillStyle = "rgb("
                    }
    
                    this.drawV = function(ctx){
                        ctx.beginPath();
                        ctx.moveTo(this.x,this.y);
                        ctx.lineTo(this.x + this.vx * this.vectorDrawSize, this.y + this.vy * this.vectorDrawSize);
                        ctx.stroke();
                    }
    
                    this.checkWallCollision = function(){
                        if((this.x <= (0 + this.r) || this.x >= (500 - this.r)) && this.canFlipX){
                            this.speedP(-this.vx * this.bouncyness, this.vy * this.bouncyness);
                            this.canFlipX = false;
                        }
    
                        if((this.y <= (0 + this.r) || this.y >= (500 - this.r)) && this.canFlipY){
                            this.speedP(this.vx * this.bouncyness, -this.vy * this.bouncyness);
                            this.canFlipY = false;
                        }
    
                        if((this.x >= (0 + this.r) && this.x <= (500 - this.r))){
                            this.canFlipX = true;
                        }
    
                        if((this.y >= (0 + this.r) && this.y <= (500 - this.r))){
                            this.canFlipY = true;
                        }
    
                        if(this.stopped){
                            this.speedP(this.vx * this.groundFriction, this.vy * this.groundFriction);
                        }
    
                        if(this.y > 500 - this.r){
                            this.y = 500 - this.r;
                        }
    
                        this.convertSpeedToMS();
                    }
    
                    this.checkCollision = function(b){
                        var distance = Math.sqrt( Math.pow(b.x - this.x, 2) + Math.pow(b.y - this.y, 2));
    
                        if((distance < (this.r + b.r)) && this.canFlipY && this.canFlipX){
    
                            let Rx = b.x - this.x;
                            let Ry = b.y - this.y;
    
                            let angle = Math.atan(Ry/Rx);
    
                            let x1 = Math.cos(angle)*(this.r+b.r);
                            let y1 = Math.sin(angle)*(this.r+b.r);
    
                            if(Rx <= 0){
    
                                y1 *= -1;
                                x1 *= -1;
                            }
    
                            x1 += this.x;
                            y1 += this.y;
    
                            var vx1 = this.vx - ((2*b.m)/(this.m + b.m)) * (( (this.vx - b.vx)*(this.x -  b.x) + (this.vy - b.vy)*( this.y - b.y) )/(Math.pow( Math.sqrt(Math.pow(this.x - b.x,2) + Math.pow(this.y - b.y,2)), 2)))*(this.x - b.x);
                            var vy1 = this.vy - ((2*b.m)/(this.m + b.m)) * (( (this.vx - b.vx)*(this.x - b.x) + (this.vy - b.vy)*( this.y - b.y) )/(Math.pow(Math.sqrt(Math.pow(this.x - b.x,2) + Math.pow(this.y - b.y,2)), 2)))*(this.y - b.y);
                
                            var vx2 = b.vx - ((2*this.m)/(this.m + b.m)) * (( (b.vx - this.vx)*(b.x - this.x) + (b.vy - this.vy)*(b.y - this.y) )/(Math.pow(Math.sqrt(Math.pow(b.x - this.x,2) + Math.pow(b.y - this.y,2)), 2)))*(b.x - this.x);
                            var vy2 = b.vy - ((2*this.m)/(this.m + b.m)) * (( (b.vx - this.vx)*(b.x - this.x) + (b.vy - this.vy)*(b.y - this.y) )/(Math.pow(Math.sqrt(Math.pow(b.x - this.x,2) + Math.pow(b.y - this.y,2)), 2)))*(b.y - this.y);
    
                            b.x = x1;
                            b.y = y1;
                            
                            this.speedP(vx1, vy1);
                            b.speedP(vx2, vy2);
                            return true;
                        }else{
                            return false;
                        }
                    }
    
                    this.gravity = function(){
                        this.vyms += 10/5000;
    
                        this.convertSpeedToP();
                    }
                }
    
                var canvas = document.getElementById("myCanvas");
                var ctx = canvas.getContext("2d");
    
                var Ccanvas = document.getElementById("collisions");
                var Cctx = Ccanvas.getContext("2d");
    
                function resetBalls(){
                    balls = [];
                	for(var i = 0; i < ballCount; i++){
                  		balls.push(new ball((variableMass)? Math.random()*maxMass : 1, (variableRadius)? Math.random()*maxRadius : maxRadius));
                    	balls[i].speed(Math.random()*0.1, Math.random()*0.1);
                	}
    	    };
    	    var balls = [];
    	    resetBalls();
    
    	(function mainLoop(){loop();setTimeout(mainLoop, document.getElementById("myRange").value)})()
    
                function loop(){
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    Cctx.clearRect(0, 0, Ccanvas.width, Ccanvas.height);
    
    
                    balls.forEach(function(bal, index) {
                        bal.gravity();
    
                        if(index == 0 && focus){
                            drawZoomed(bal);
                        }
    
                        balls.forEach(function(bal1, index1) {
                            if(index1 != index && index1 > index){
                                if(index == 0 && focus){
                                    drawCollision(bal,bal1);
                                    bal.checkCollision(bal1);
                                }
                                else{
                                    bal.checkCollision(bal1);
                                }
                            }
                        });
    
                        bal.checkWallCollision();
                    });
    
                    balls.forEach(function(bal,i){
                        bal.draw(ctx, (i == 0 && focus)? "#FF0000" : "");
                        if(drawVectors){
                            bal.drawV(ctx);
                        }
                        bal.move();
    			        ctx.fillStyle = "#FFF";			
                    });
                    
                };
    
            function drawZoomed(a){
                Cctx.fillStyle = "#FF0000";
                Cctx.beginPath();
                Cctx.arc(250,250, a.r*zoomFocus, 0, 2 * Math.PI);
                Cctx.fill();
                Cctx.closePath();
                Cctx.stroke();
                Cctx.fillStyle = "#FFF";
    
                /*Cctx.beginPath();
                Cctx.moveTo(250,250);
                Cctx.lineTo(250 + a.vx * a.vectorDrawSize*zoom, 250 + a.vy * a.vectorDrawSize*zoom);
                Cctx.stroke();*/
            }
    
            function drawCollision(a,b){
                //Cctx.clearRect(0, 0, Ccanvas.width, Ccanvas.height);
    
                //drawZoomed(a, zoom);
    
                let x1 = 250 + (b.x - a.x)*zoomFocus;
                let y1 = 250 + (b.y - a.y)*zoomFocus;
    
                Cctx.beginPath();
                Cctx.arc(x1,y1, b.r*zoomFocus, 0, 2 * Math.PI);
                Cctx.fill();
                Cctx.closePath();
                Cctx.stroke();
    
                Cctx.beginPath();
                Cctx.moveTo(0,(500-a.y)*zoomFocus+250);
                Cctx.lineTo(500,(500-a.y)*zoomFocus+250);
                Cctx.stroke();
    
                Cctx.beginPath();
                Cctx.moveTo(0,250-(a.y)*zoomFocus);
                Cctx.lineTo(500,250-(a.y)*zoomFocus);
                Cctx.stroke();
    
                Cctx.beginPath();
                Cctx.moveTo((500-a.x)*zoomFocus+250, 0);
                Cctx.lineTo((500-a.x)*zoomFocus+250, 500);
                Cctx.stroke();
    
                Cctx.beginPath();
                Cctx.moveTo(250-(a.x)*zoomFocus, 0);
                Cctx.lineTo(250-(a.x)*zoomFocus, 500);
                Cctx.stroke();
            }
    
            </script>
        </div>
        <script src="javascript/functionality.js"></script>
    </body>
