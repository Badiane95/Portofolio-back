import React from 'react';

const PortfolioSkills = () => {
  const skills = [
    {
      icon: "code",
      title: "Développement Web",
      description: "Maîtrise des langages HTML, CSS, JavaScript, PHP et utilisation de frameworks comme Bootstrap."
    },
    {
      icon: "paint-brush",
      title: "Design Graphique",
      description: "Compétences en création de visuels avec Adobe Photoshop, Illustrator et Figma."
    },
    {
      icon: "database",
      title: "Gestion de Base de Données",
      description: "Conception et gestion de bases de données avec MySQL et implémentation via PHP."
    },
    {
      icon: "video",
      title: "Production Vidéo",
      description: "Réalisation et montage vidéo avec Adobe Premiere Pro et After Effects."
    },
    {
      icon: "project-diagram",
      title: "Gestion de Projet",
      description: "Organisation et planification de projets multimédias en équipe avec des outils comme Trello et Notion."
    },
    {
      icon: "search",
      title: "SEO et Web Marketing",
      description: "Optimisation de sites web pour le référencement et stratégie de communication digitale."
    }
  ];

  return (
    <div className="bg-violet-50 min-h-screen py-12">
      <div className="container mx-auto px-4">
        <h2 className="text-4xl font-bold text-center text-violet-800 mb-10">Mes Compétences</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {skills.map((skill, index) => (
            <div 
              key={index} 
              className="bg-white rounded-lg shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-xl border-2 border-violet-200"
            >
              <div className="text-center">
                <div className="w-16 h-16 mx-auto mb-4 bg-violet-100 rounded-full flex items-center justify-center">
                  <i className={`fas fa-${skill.icon} text-3xl text-violet-600`}></i>
                </div>
                <h3 className="text-xl font-semibold text-violet-800 mb-3">{skill.title}</h3>
                <p className="text-gray-600">{skill.description}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default PortfolioSkills;