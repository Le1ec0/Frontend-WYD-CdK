<<<<<<< HEAD
///////////////////////////////
//           Utils           //
///////////////////////////////

var Range = (function () {
  function Range(min, max) {
    this.min = min;
    this.max = max;
  }
  Range.prototype.getRandom = function () {
    return this.min + (this.max - this.min) * Math.random();
  };
  return Range;
}());

var Vector2 = (function () {
  function Vector2(x, y) {
    if (x == undefined)
      x = 0;
    if (y == undefined)
      y = 0;
    this.x = x;
    this.y = y;
  }
  Vector2.prototype.minus = function (position) {
    return new Vector2(this.x - position.x, this.y - position.y);
  };
  Vector2.prototype.plus = function (position) {
    return new Vector2(this.x + position.x, this.y + position.y);
  };
  Vector2.prototype.rotateDeg = function (angle) {
    var angleRadians = (angle * Math.PI) / 180;
    return this.rotate(angleRadians);
  };
  Vector2.prototype.rotate = function (angle) {
    var ca = Math.cos(angle);
    var sa = Math.sin(angle);
    return new Vector2(ca * this.x + sa * this.y, sa * this.x - ca * this.y);
  };
  Vector2.prototype.divide = function (value) {
    return this.multiply(1 / value);
  };
  Vector2.prototype.multiply = function (value) {
    return new Vector2(this.x * value, this.y * value);
  };
  Vector2.prototype.invert = function () {
    return new Vector2(-this.x, -this.y);
  };
  Vector2.prototype.magnitude = function () {
    return Math.sqrt(this.x * this.x + this.y * this.y);
  };
  Vector2.prototype.angle = function () {
    return Math.atan2(-this.y, this.x) * (180 / Math.PI);
  };
  Vector2.up = function () {
    return new Vector2(0, 1);
  };
  Vector2.down = function () {
    return Vector2.up().invert();
  };
  Vector2.left = function () {
    return new Vector2(-1, 0);
  };
  Vector2.right = function () {
    return Vector2.left().invert();
  };
  Vector2.zero = function () {
    return new Vector2();
  };
  return Vector2;
}());

//////////////////////////////////
//           Particle           //
//////////////////////////////////

var ParticleData = (function () {
  function ParticleData(emissionRate, duration, totalParticles, size, energy, velocity, randomVelocity, gravity, randomRotation, angularSpeed, randomAngularSpeed, oneShot, texture, startOpacity, endOpacity, spawnBorder) {
    this.emissionRate = emissionRate;
    this.duration = duration;
    this.totalParticles = totalParticles;
    this.size = size;
    this.energy = energy;
    this.velocity = velocity;
    this.randomVelocity = randomVelocity;
    this.gravity = gravity;
    this.randomRotation = randomRotation;
    this.angularSpeed = angularSpeed;
    this.randomAngularSpeed = randomAngularSpeed;
    this.oneShot = oneShot;
    this.texture = texture;
    this.startOpacity = startOpacity;
    this.endOpacity = endOpacity;
    this.spawnBorder = spawnBorder;
  }
  return ParticleData;
}());

//////////////////////////////////
//           Examples           //
//////////////////////////////////
var PARTICLES = {};
PARTICLES["coin-particle"] =
  new ParticleData(20, //emissionRate
  1000, //duration
  30, //totalParticles
  new Range(0.1, 0.3), //size
  new Range(0.3, 0.5), //energy
  new Vector2(0, 1), //velocity
  new Vector2(1, 0.5), //randomVelocity
  5, //gravity
  true, //Random Rotation
  720, //angularSpeed
  720, //randomAngularSpeed
  false, //oneShot
  'url("app/assets/general/icon/coin.html")', //texture
  new Range(1, 1), //start opacity
  new Range(0, 0), //end opacity
  false //border spawn
  );
PARTICLES["firefly-particle"] =
  new ParticleData(30, //emissionRate
  1000, //duration
  50, //totalParticles
  new Range(0.35, 0.48), //size
  new Range(1, 2), //energy
  new Vector2(0, 0), //velocity
  new Vector2(3, .5), //randomVelocity
  6, //gravity
  true, //Random Rotation
  0, //angularSpeed
  0, //randomAngularSpeed
  false, //oneShot
  'url("app/assets/general/particle/yellow.html")', //texture
  new Range(1, 1), //start opacity
  new Range(0, 0), //end opacity
  false //border spawn
  );
PARTICLES["border-bright"] =
  new ParticleData(0.2, //emissionRate
  1000, //duration
  4, //totalParticles
  new Range(0.35, 0.48), //size
  new Range(3, 5), //energy
  new Vector2(0, 0), //velocity
  new Vector2(0, 0), //randomVelocity
  0, //gravity
  true, //Random Rotation
  60, //angularSpeed
  0, //randomAngularSpeed
  false, //oneShot
  'url("app/assets/general/particle/yellow.html")', //texture
  new Range(1, 1), //start opacity
  new Range(0, 0), //end opacity
  true //border spawn
  );

////////////////////////////////
//           System           //
////////////////////////////////

var ParticleSystem = (function () {
  function ParticleSystem(type,effectName,transition,dom) {

    this.isSprite = function() {
      return this.type == "ParticleType.Sprite";
    };
    this.isCSS = function() {
      return this.type == "ParticleType.html";
    };

    this.initParticles = function() {
      if(this.isCSS()) {
        this.availableParticles = [];
        var buffer = [];
        for(var i = 0; i < this.particleData.totalParticles;i++) {
          var element = document.createElement("particle");
          var size = Math.ceil(this.particleData.size.getRandom()*Math.max(this.dom.clientWidth, this.dom.clientHeight));
          element.style.width = size+"px";
          element.style.height = size+"px";
          element.style.marginLeft = -size/2+"px";
          element.style.marginTop = -size/2+"px";
          element.style.backgroundImage = this.particleData.texture;
          element.style.opacity = "0";
          this.dom.appendChild(element);
          this.availableParticles.push(element);
        }

  		  this.timeline = new TimelineMax({qty: 80, duration: this.particleData.duration});
        this.emittedCount = 0;
        this.startTime = new Date();
        if(this.particleData.oneShot) {
          while(this.availableParticles.length > 0) {
            var particle = this.availableParticles.shift();
            this.playParticle(particle, 0);
          }
        } else {
          this.updateId = setInterval(function(){this.updateParticles();}.bind(this), 100);
        }
      }
    };

    this.updateParticles = function () {
      var seconds = (new Date().getTime() - this.startTime.getTime()) / 1000;
      while (this.availableParticles.length > 0 && this.emittedCount < this.particleData.emissionRate * seconds) {
        var particle = this.availableParticles.shift();
        this.playParticle(particle, seconds);
        this.emittedCount += 1;
      }
    };

    this.playParticle = function (particle, delay) {
      var maxSize = Math.max(this.dom.clientWidth, this.dom.clientHeight);
      var x = Math.random() * this.dom.clientWidth;
      var y = Math.random() * this.dom.clientHeight;
      if (this.particleData.spawnBorder) {
        var random = Math.floor(Math.random() * 4);
        switch (random) {
          case 0:
            x *= 1;
            y = 0;
            break;
          case 1:
            x = 0;
            y *= 1;
            break;
          case 2:
            x *= 1;
            y = this.dom.clientHeight;
            break;
          case 3:
            x = this.dom.clientWidth;
            y *= 1;
            break;
        }
      }
      TweenLite.set(particle, { x: x, y: y });
      var duration = this.particleData.energy.getRandom();
      var startVelocity = this.particleData.velocity;
      var changeVelocity = new Vector2(this.particleData.randomVelocity.x * (2 * Math.random() - 1), this.particleData.randomVelocity.y * (2 * Math.random() - 1));
      var velocity = startVelocity.plus(changeVelocity);
      velocity = velocity.multiply(maxSize);
      var angle = velocity.angle();
      var magnitude = velocity.magnitude();
      var rotation = 0;
      if (this.particleData.randomRotation)
        rotation = Math.random() * 360;
      var finalRotation = rotation + (this.particleData.angularSpeed + (2 * Math.random() - 1) * this.particleData.randomAngularSpeed) * duration;
      this.timeline.fromTo(particle, duration, {
        opacity: this.particleData.startOpacity.getRandom(),
        rotation: rotation
      }, {
        immediateRender: false,
        opacity: this.particleData.endOpacity.getRandom(),
        rotation: finalRotation,
        physics2D: {
          velocity: magnitude,
          angle: angle,
          gravity: this.particleData.gravity * maxSize
        },
        onComplete: function (particle) {
          this.availableParticles.push(particle);
        }.bind(this, particle)
      }, delay);
    };

    this.type = type;
    this.effectName = effectName;
    this.transition = transition;
    this.dom = dom;
    this.updateId = -1;
    this.emittedCount = 0;
    this.particleData = PARTICLES[this.effectName];
    this.initParticles();
  }
  return ParticleSystem;
}());


//////////////////////////////////
//           Function           //
//////////////////////////////////
function myFunction () {
  //new ParticleSystem("ParticleType.CSS","border-bright",true,document.body);
};
=======
///////////////////////////////
//           Utils           //
///////////////////////////////

var Range = (function () {
  function Range(min, max) {
    this.min = min;
    this.max = max;
  }
  Range.prototype.getRandom = function () {
    return this.min + (this.max - this.min) * Math.random();
  };
  return Range;
}());

var Vector2 = (function () {
  function Vector2(x, y) {
    if (x == undefined)
      x = 0;
    if (y == undefined)
      y = 0;
    this.x = x;
    this.y = y;
  }
  Vector2.prototype.minus = function (position) {
    return new Vector2(this.x - position.x, this.y - position.y);
  };
  Vector2.prototype.plus = function (position) {
    return new Vector2(this.x + position.x, this.y + position.y);
  };
  Vector2.prototype.rotateDeg = function (angle) {
    var angleRadians = (angle * Math.PI) / 180;
    return this.rotate(angleRadians);
  };
  Vector2.prototype.rotate = function (angle) {
    var ca = Math.cos(angle);
    var sa = Math.sin(angle);
    return new Vector2(ca * this.x + sa * this.y, sa * this.x - ca * this.y);
  };
  Vector2.prototype.divide = function (value) {
    return this.multiply(1 / value);
  };
  Vector2.prototype.multiply = function (value) {
    return new Vector2(this.x * value, this.y * value);
  };
  Vector2.prototype.invert = function () {
    return new Vector2(-this.x, -this.y);
  };
  Vector2.prototype.magnitude = function () {
    return Math.sqrt(this.x * this.x + this.y * this.y);
  };
  Vector2.prototype.angle = function () {
    return Math.atan2(-this.y, this.x) * (180 / Math.PI);
  };
  Vector2.up = function () {
    return new Vector2(0, 1);
  };
  Vector2.down = function () {
    return Vector2.up().invert();
  };
  Vector2.left = function () {
    return new Vector2(-1, 0);
  };
  Vector2.right = function () {
    return Vector2.left().invert();
  };
  Vector2.zero = function () {
    return new Vector2();
  };
  return Vector2;
}());

//////////////////////////////////
//           Particle           //
//////////////////////////////////

var ParticleData = (function () {
  function ParticleData(emissionRate, duration, totalParticles, size, energy, velocity, randomVelocity, gravity, randomRotation, angularSpeed, randomAngularSpeed, oneShot, texture, startOpacity, endOpacity, spawnBorder) {
    this.emissionRate = emissionRate;
    this.duration = duration;
    this.totalParticles = totalParticles;
    this.size = size;
    this.energy = energy;
    this.velocity = velocity;
    this.randomVelocity = randomVelocity;
    this.gravity = gravity;
    this.randomRotation = randomRotation;
    this.angularSpeed = angularSpeed;
    this.randomAngularSpeed = randomAngularSpeed;
    this.oneShot = oneShot;
    this.texture = texture;
    this.startOpacity = startOpacity;
    this.endOpacity = endOpacity;
    this.spawnBorder = spawnBorder;
  }
  return ParticleData;
}());

//////////////////////////////////
//           Examples           //
//////////////////////////////////
var PARTICLES = {};
PARTICLES["coin-particle"] =
  new ParticleData(20, //emissionRate
  1000, //duration
  30, //totalParticles
  new Range(0.1, 0.3), //size
  new Range(0.3, 0.5), //energy
  new Vector2(0, 1), //velocity
  new Vector2(1, 0.5), //randomVelocity
  5, //gravity
  true, //Random Rotation
  720, //angularSpeed
  720, //randomAngularSpeed
  false, //oneShot
  'url("app/assets/general/icon/coin.html")', //texture
  new Range(1, 1), //start opacity
  new Range(0, 0), //end opacity
  false //border spawn
  );
PARTICLES["firefly-particle"] =
  new ParticleData(30, //emissionRate
  1000, //duration
  50, //totalParticles
  new Range(0.35, 0.48), //size
  new Range(1, 2), //energy
  new Vector2(0, 0), //velocity
  new Vector2(3, .5), //randomVelocity
  6, //gravity
  true, //Random Rotation
  0, //angularSpeed
  0, //randomAngularSpeed
  false, //oneShot
  'url("app/assets/general/particle/yellow.html")', //texture
  new Range(1, 1), //start opacity
  new Range(0, 0), //end opacity
  false //border spawn
  );
PARTICLES["border-bright"] =
  new ParticleData(0.2, //emissionRate
  1000, //duration
  4, //totalParticles
  new Range(0.35, 0.48), //size
  new Range(3, 5), //energy
  new Vector2(0, 0), //velocity
  new Vector2(0, 0), //randomVelocity
  0, //gravity
  true, //Random Rotation
  60, //angularSpeed
  0, //randomAngularSpeed
  false, //oneShot
  'url("app/assets/general/particle/yellow.html")', //texture
  new Range(1, 1), //start opacity
  new Range(0, 0), //end opacity
  true //border spawn
  );

////////////////////////////////
//           System           //
////////////////////////////////

var ParticleSystem = (function () {
  function ParticleSystem(type,effectName,transition,dom) {

    this.isSprite = function() {
      return this.type == "ParticleType.Sprite";
    };
    this.isCSS = function() {
      return this.type == "ParticleType.html";
    };

    this.initParticles = function() {
      if(this.isCSS()) {
        this.availableParticles = [];
        var buffer = [];
        for(var i = 0; i < this.particleData.totalParticles;i++) {
          var element = document.createElement("particle");
          var size = Math.ceil(this.particleData.size.getRandom()*Math.max(this.dom.clientWidth, this.dom.clientHeight));
          element.style.width = size+"px";
          element.style.height = size+"px";
          element.style.marginLeft = -size/2+"px";
          element.style.marginTop = -size/2+"px";
          element.style.backgroundImage = this.particleData.texture;
          element.style.opacity = "0";
          this.dom.appendChild(element);
          this.availableParticles.push(element);
        }

  		  this.timeline = new TimelineMax({qty: 80, duration: this.particleData.duration});
        this.emittedCount = 0;
        this.startTime = new Date();
        if(this.particleData.oneShot) {
          while(this.availableParticles.length > 0) {
            var particle = this.availableParticles.shift();
            this.playParticle(particle, 0);
          }
        } else {
          this.updateId = setInterval(function(){this.updateParticles();}.bind(this), 100);
        }
      }
    };

    this.updateParticles = function () {
      var seconds = (new Date().getTime() - this.startTime.getTime()) / 1000;
      while (this.availableParticles.length > 0 && this.emittedCount < this.particleData.emissionRate * seconds) {
        var particle = this.availableParticles.shift();
        this.playParticle(particle, seconds);
        this.emittedCount += 1;
      }
    };

    this.playParticle = function (particle, delay) {
      var maxSize = Math.max(this.dom.clientWidth, this.dom.clientHeight);
      var x = Math.random() * this.dom.clientWidth;
      var y = Math.random() * this.dom.clientHeight;
      if (this.particleData.spawnBorder) {
        var random = Math.floor(Math.random() * 4);
        switch (random) {
          case 0:
            x *= 1;
            y = 0;
            break;
          case 1:
            x = 0;
            y *= 1;
            break;
          case 2:
            x *= 1;
            y = this.dom.clientHeight;
            break;
          case 3:
            x = this.dom.clientWidth;
            y *= 1;
            break;
        }
      }
      TweenLite.set(particle, { x: x, y: y });
      var duration = this.particleData.energy.getRandom();
      var startVelocity = this.particleData.velocity;
      var changeVelocity = new Vector2(this.particleData.randomVelocity.x * (2 * Math.random() - 1), this.particleData.randomVelocity.y * (2 * Math.random() - 1));
      var velocity = startVelocity.plus(changeVelocity);
      velocity = velocity.multiply(maxSize);
      var angle = velocity.angle();
      var magnitude = velocity.magnitude();
      var rotation = 0;
      if (this.particleData.randomRotation)
        rotation = Math.random() * 360;
      var finalRotation = rotation + (this.particleData.angularSpeed + (2 * Math.random() - 1) * this.particleData.randomAngularSpeed) * duration;
      this.timeline.fromTo(particle, duration, {
        opacity: this.particleData.startOpacity.getRandom(),
        rotation: rotation
      }, {
        immediateRender: false,
        opacity: this.particleData.endOpacity.getRandom(),
        rotation: finalRotation,
        physics2D: {
          velocity: magnitude,
          angle: angle,
          gravity: this.particleData.gravity * maxSize
        },
        onComplete: function (particle) {
          this.availableParticles.push(particle);
        }.bind(this, particle)
      }, delay);
    };

    this.type = type;
    this.effectName = effectName;
    this.transition = transition;
    this.dom = dom;
    this.updateId = -1;
    this.emittedCount = 0;
    this.particleData = PARTICLES[this.effectName];
    this.initParticles();
  }
  return ParticleSystem;
}());


//////////////////////////////////
//           Function           //
//////////////////////////////////
function myFunction () {
  //new ParticleSystem("ParticleType.CSS","border-bright",true,document.body);
};
>>>>>>> 1695ed2bf62b26da8258d5b399e7b009bed0bdf1
